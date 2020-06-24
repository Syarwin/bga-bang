<?php

/*
 * BangLog: a class that allows to log some actions
 *   and then fetch these actions latter
 */
class BangLog extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }


////////////////////////////////
////////////////////////////////
//////////   Stats   ///////////
////////////////////////////////
////////////////////////////////

  /*
   * initStats: initialize statistics to 0 at start of game
   */
  public function initStats($players)
  {
    /*
    $this->game->initStat('table', 'move', 0);
    $this->game->initStat('table', 'buildBlock', 0);
    $this->game->initStat('table', 'buildDome', 0);
    $this->game->initStat('table', 'buildTower', 0);

    foreach ($players as $pId => $player) {
      $this->game->initStat('player', 'playerPower', 0, $pId);
      $this->game->initStat('player', 'usePower', 0, $pId);
      $this->game->initStat('player', 'move', 0, $pId);
      $this->game->initStat('player', 'moveUp', 0, $pId);
      $this->game->initStat('player', 'moveDown', 0, $pId);
      $this->game->initStat('player', 'buildBlock', 0, $pId);
      $this->game->initStat('player', 'buildDome', 0, $pId);
    }
    */
  }

  /*
   * gameEndStats: compute end-of-game statistics
   */
  public function gameEndStats()
  {
//    $this->game->setStat($this->game->board->getCompleteTowerCount(), 'buildTower');
  }

  public function incrementStats($stats, $value = 1)
  {
    foreach ($stats as $pId => $names) {
      foreach ($names as $name) {
        if ($pId == 'table') {
          $pId = null;
        }
        $this->game->incStat($value, $name, $pId);
      }
    }
  }


////////////////////////////////
////////////////////////////////
//////////   Adders   //////////
////////////////////////////////
////////////////////////////////

  /*
   * insert: add a new log entry
   * params:
   *   - $playerId: the player who is making the action
   *   - $pieceId : the piece whose is making the action
   *   - string $action : the name of the action
   *   - array $args : action arguments (eg space)
   */
  public function insert($playerId, $pieceId, $action, $args = [])
  {
    $playerId = $playerId == -1 ? $this->game->getActivePlayerId() : $playerId;
    $moveId = self::getUniqueValueFromDB("SELECT `global_value` FROM `global` WHERE `global_id` = 3");
    $round = $this->game->getGameStateValue("currentRound");

/*
    if ($action == 'move') {
      $args['stats'] = [
        'table' => ['move'],
        $playerId => ['move'],
      ];
      if ($args['to']['z'] > $args['from']['z']) {
        $args['stats'][$playerId][] = 'moveUp';
      } else if ($args['to']['z'] < $args['from']['z']) {
        $args['stats'][$playerId][] = 'moveDown';
      }
    } else if ($action == 'build') {
      $statName = $args['to']['arg'] == 3 ? 'buildDome' : 'buildBlock';
      $args['stats'] = [
        'table' => [$statName],
        $playerId => [$statName],
      ];
    }
*/
    if (array_key_exists('stats', $args)) {
      $this->incrementStats($args['stats']);
    }

    $actionArgs = json_encode($args);

    self::DbQuery("INSERT INTO log (`round`, `move_id`, `player_id`, `piece_id`, `action`, `action_arg`) VALUES ('$round', '$moveId', '$playerId', '$pieceId', '$action', '$actionArgs')");
  }



  /*
   * addAction: add a new action to log
   */
  public function addAction($action, $args = [])
  {
    $this->insert(-1, 0, $action, $args);
  }


/////////////////////////////////
/////////////////////////////////
//////////   Getters   //////////
/////////////////////////////////
/////////////////////////////////

  /*
   * getLastActions : get works and actions of player (used to cancel previous action)
   */
  public function getLastActions($actions = ['build', 'usedPower', 'useTile'], $pId = null, $offset = null)
  {
    $pId = $pId ?? $this->game->getActivePlayerId();
    $offset = $offset ?? 0;
    $actionsNames = "'" . implode("','", $actions) . "'";

    return self::getObjectListFromDb("SELECT * FROM log WHERE `action` IN ($actionsNames) AND `player_id` = '$pId' AND `round` = (SELECT round FROM log WHERE `player_id` = $pId AND `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) - $offset ORDER BY log_id DESC");
  }
}
