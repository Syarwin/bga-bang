<?php

/*
 * KingdomBuilderPlayerManager: all utility functions concerning players
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
    $rows = self::getObjectListFromDb($sql);

    $players = [];
    foreach ($rows as $row) {
      $player = new BangPlayer($this->game, $row);
      $players[] = $player;
    }
    return $players;
  }

  /*
   * getPlayerCount: return the number of players
   */
  public function getPlayerCount()
  {
    return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM player"));
  }


  /*
   * getUiData : get all ui data of all players : id, no, name, team, color, powers list, farmers
   */
  public function getUiData($currentPlayerId)
  {
    $ui = [];
    foreach ($this->getPlayers() as $player)
       $ui[] = $player->getUiData($currentPlayerId);

    return $ui;
  }
}
