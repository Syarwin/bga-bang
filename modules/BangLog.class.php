<?php

/*
 * BangLog: a class that allows to log some actions
 *   and then fetch these actions latter
 */
class BangLog extends APP_GameClass
{
  public static function getCurrentTurn(){
    $turns = self::getObjectListFromDb("SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC");
    return empty($turns)? 0 : (int) $turns[0]["turn"];
  }

////////////////////////////////
////////////////////////////////
//////////   Stats   ///////////
////////////////////////////////
////////////////////////////////

  /*
   * initStats: initialize statistics to 0 at start of game
   */
  public static function initStats($bplayers)
  {
    /*
    $this->game->initStat('table', 'move', 0);
    $this->game->initStat('table', 'buildBlock', 0);
    $this->game->initStat('table', 'buildDome', 0);
    $this->game->initStat('table', 'buildTower', 0);

    foreach ($bplayers as $pId => $player) {
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
  public static function gameEndStats()
  {
//    $this->game->setStat($this->game->board->getCompleteTowerCount(), 'buildTower');
  }

  public static function incrementStats($stats, $value = 1)
  {
    foreach ($stats as $pId => $names) {
      foreach ($names as $name) {
        if ($pId == 'table') {
          $pId = null;
        }
        bang::$instance::incStat($value, $name, $pId);
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
   *   - $cardId : the card whose is making the action
   *   - string $action : the name of the action
   *   - array $args : action arguments (eg space)
   */
  public static function insert($playerId, $cardId, $action, $args = [])
  {
    $playerId = $playerId == -1 ? bang::$instance->getActivePlayerId() : $playerId;
    $turn = self::getCurrentTurn() + ($action == "startTurn"? 1 : 0);

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
      self::incrementStats($args['stats']);
    }

    $actionArgs = json_encode($args);

    self::DbQuery("INSERT INTO log (`turn`, `player_id`, `card_id`, `action`, `action_arg`) VALUES ('$turn', '$playerId', '$cardId', '$action', '$actionArgs')");
  }



  /*
   * addAction: add a new action to log
   */
  public static function addAction($action, $args = [])
  {
    self::insert(-1, 0, $action, $args);
  }


  /*
   * starTurn: logged whenever a player start its turn, very useful to fetch last actions
   */
  public static function startTurn()
  {
    self::insert(-1, 0, 'startTurn');
  }

  public static function addCardPlayed($player, $card, $args)
  {
    self::insert($player->getId(), $card->getId(), "cardPlayed", $args);
  }

/////////////////////////////////
/////////////////////////////////
//////////   Getters   //////////
/////////////////////////////////
/////////////////////////////////

  /*
   * getLastActions : get works and actions of player (used to cancel previous action)
   */
  public static function getLastActions($actions = [], $pId = null, $offset = null)
  {
    $player = is_null($pId)? "" : "AND `player_id` = '$pId'";
    $offset = $offset ?? 0;
    $actionsNames = "'" . implode("','", $actions) . "'";

    return self::getObjectListFromDb("SELECT * FROM log WHERE `action` IN ($actionsNames) $player AND `turn` = (SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) - $offset ORDER BY log_id DESC");
  }

  public static function getLastAction($action, $pId = null, $offset = null)
  {
    $actions = self::getLastActions([$action], $pId, $offset);
    return count($actions) > 0 ? json_decode($actions[0]['action_arg'], true) : null;
  }


  public static function getPlayerTurn()
  {
    $turn = self::getObjectFromDb("SELECT * FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1");
    return is_null($turn)? null : $turn['player_id'];
  }


  public static function getCurrentCard()
  {
    $action = self::getObjectFromDb("SELECT * FROM log WHERE `action` = 'cardPlayed' AND `turn` = (SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) ORDER BY log_id DESC LIMIT 1");
    return is_null($action)? null : $action["card_id"];
  }


  public static function getReactPlayers()
  {
    $players = array_keys(self::getLastAction("react"));
    return count($players > 1)? $players : $players[0];
  }

  public static function getReactArgs()
  {
    $args = self::getLastAction("react");
    return count($args > 1)? $args : ['active' => array_values($args)[0] ];
  }

}
