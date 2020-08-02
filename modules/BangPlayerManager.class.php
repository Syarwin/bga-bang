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
		self::DbQuery('DELETE FROM player');
		$gameInfos = $this->game->getGameinfos();
		$sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
		$values = [];
		$i = 0;
		foreach ($players as $pId => $player) {
			$color = $gameInfos['player_colors'][$i++];
			$values[] = "('" . $pId . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
		}
		self::DbQuery($sql . implode($values, ','));
		$this->game->reloadPlayersBasicInfos();

		foreach($this->getPlayers() as $player){
			$player->setupNewGame();
		}
	}


	/*
	 * characterClasses : for each character Id, the corresponding class name
	 */
	public static $classes = [
		LUCKY_DUKE => 'LuckyDuke',
		EL_GRINGO => 'ElGringo',
		SID_KETCHUM => 'SidKetchum',
		BART_CASSIDY => 'BartCassidy',
		JOURDONNAIS => 'Jourdonnais',
		PAUL_REGRET => 'PaulRegret',
		BLACK_JACK => 'BlackJack',
		PEDRO_RAMIREZ => 'PedroRamirez',
		SUZY_LAFAYETTE => 'SuzyLafayette',
		KIT_CARLSON => 'KitCarlson',
		VULTURE_SAM => 'VultureSam',
		JESSE_JONES => 'JesseJones',
		CALAMITY_JANET => 'CalamityJanet',
		SLAB_THE_KILLER => 'SlabtheKiller',
		WILLY_THE_KID => 'WillytheKid',
		ROSE_DOOLAN => 'RoseDoolan',
		
		/*MOLLY_STARK => 'MollyStark',
		APACHE_KID => 'ApacheKid',
		ELENA_FUENTE => 'ElenaFuente',
		TEQUILA_JOE => 'TequilaJoe',
		VERA_CUSTER => 'VeraCuster',
		BILL_NOFACE => 'BillNoface',
		HERB_HUNTER => 'HerbHunter',
		PIXIE_PETE => 'PixiePete',
		SEAN_MALLORY => 'SeanMallory',
		PAT_BRENNAN => 'PatBrennan',
		JOSE_DELGADO => 'JoseDelgado',
		CHUCK_WENGAM => 'ChuckWengam',
		BELLE_STAR => 'BelleStar',
		DOC_HOLYDAY => 'DocHolyday',
		GREG_DIGGER => 'GregDigger',*/
	];


	/*
	 * getPlayer : returns the SantoriniPlayer object for the given player ID
	 */
	public function getPlayer($playerId = null)
	{
		$playerId = $playerId ?? $this->game->getActivePlayerId();
		$players = $this->getPlayers([$playerId]);
		return $players[0];
	}

	/*
	 * getPlayers : Returns array of SantoriniPlayer objects for all/specified player IDs
	 */
	public function getPlayers($playerIds = null)
	{
		$sql = "SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_no no FROM player";
		if (is_array($playerIds)) {
			$sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
		}
		$rows = self::getObjectListFromDB($sql);

		$players = [];
		foreach ($rows as $row) {
			$player = new BangPlayer($row);
			$players[] = $player;
		}
		return $players;
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
	 * getSheriff : Returns the id of the Sheriff
	 */
	public static function getSheriff() {
		return self::getUniqueValueFromDB( "SELECT id FROM playerinfo WHERE role=0" );
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
	 * getUiData : get all ui data of all players : id, hp, max_hp no, player_name, player_color, character, powers(character effect), hand(count)
	 */
	public static function getUiData($playerIds = null)
	{
		$sql = "SELECT player_id, player_score hp, max_hp, player_name, player_color, character_id FROM player LEFT JOIN playerinfo ON player.player_id = playerinfo.id";
		if (is_array($playerIds)) {
			$sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
		}
		$players = self::getCollectionFromDb($sql);

		
		foreach ($players as $id=>$player) {
			$char = new BangPlayerManager::$classes[$player['character_id']]();
			$players[$id]['character'] = $char->name;
			$players[$id]['powers'] = $char->text;
			$players[$id]['hand'] = self::getUniqueValueFromDB("SELECT COUNT(*) FROM cards WHERE card_position=$id");
		}
		return $players;
	}
}
