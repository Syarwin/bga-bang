<?php

/*
 * BangPlayerManager: all utility functions concerning players
 */

require_once('BangPlayer.class.php');

class BangPlayerManager extends APP_GameClass
{
	public $game;
	public function __construct($game)
	{
		$this->game = $game;
	}


	public function setupNewGame($players)
	{
		// Draw the roles
		$allRoles = [SHERIFF, OUTLAW, OUTLAW, RENEGADE, DEPUTY, OUTLAW, DEPUTY];
		$roles = array_slice($allRoles,0,count($players));
		shuffle($roles);

		// Create basic info player table
		self::DbQuery('DELETE FROM player');
		$gameInfos = $this->game->getGameinfos();
		$sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_role, player_bullets) VALUES ';
		$values = [];
		$i = 0;
		foreach ($players as $pId => $player) {
			$color = $gameInfos['player_colors'][$i];
			$role = $roles[$i];
			$values[] = "('$pId','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "', '$role', 0)";
			$i++;
		}
		self::DbQuery($sql . implode($values, ','));
		$this->game->reloadPlayersBasicInfos();

		// Setup each player : add character and draw cards
		foreach($this->get() as $player){
			$player->setupNewGame();
		}
	}




	/*
	 * get : returns array of BangPlayer objects for all/specified player IDs
	 */
	public function get($playerId = null)
	{
		$sql = "SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_no no, player_role role, player_bullets bullets FROM player";
		if (!is_null($playerId)){
			$playerIds = is_array($playerIds)? $playerIds : [$playerIds];
			$sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
		}
		$rows = self::getObjectListFromDB($sql);

		$players = [];
		foreach ($rows as $row) {
			$player = new BangPlayer($this->game, $row);
			$players[] = $player;
		}
		return $players;
	}


	/*
	 * getCurrent : return BangPlayer object of current player
	 */
	public function getCurrent()
	{
		$players = $this->getPlayers([$this->game->getActivePlayerId()]);
		return $players[0];
	}


	/*
	 * getSheriff : return BangPlayer object of sheriff player
	 */
	public function getSheriff()
	{
		foreach($this->get() as $player){
			if($player->getRole() == SHERIFF)
				return $player;
		}
		throw new BgaVisibleSystemException('Could not find sheriff');
	}




	/**
	 * returns an array of the ids of all living players
	 */
	public static function getLivingPlayers($exept = null) {
		$sql = "SELECT player_id FROM player WHERE player_eliminated=0";
		if($exept != null) $sql.= " AND player_id != $exept";
		return self::getObjectListFromDB($sql);
	}


	/**
	 * getPlayerTurn : Returns the id of the player whos turn it is
	 */
	public static function getPlayerTurn() {
		return self::getUniqueValueFromDB("SELECT game_player FROM game");
	}

	/**
	 * getCharacters : returns an associative array with all players and their characters (player_id => character_id)
	 */
	public static function getCharacters() {
		return self::getCollectionFromDB("SELECT id, character_id FROM playerinfo");
	}



	/*
	 * getPlayerCount: return the number of players
	 */
	public static function getPlayerCount()
	{
		return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM player"));
	}



	/*
   * getUiData : get all ui data of all players : id, no, name, team, color, powers list
   */
  public function getUiData($currentPlayerId)
  {
    $ui = [];
    foreach ($this->get() as $player) {
      $ui[] = $player->getUiData($currentPlayerId);
    }
    return $ui;
  }
}
