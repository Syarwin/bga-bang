<?php
namespace BANG\Core;
use bang;

/*
 * Log: a class that allows to log some actions
 *   and then fetch these actions latter
 */
class Log extends \APP_GameClass
{
  public static function getCurrentTurn()
  {
    $turns = self::getObjectListFromDb("SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC");
    return empty($turns) ? 0 : (int) $turns[0]['turn'];
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
    $playerId = $playerId == -1 ? bang::get()->getActivePlayerId() : $playerId;
    $turn = self::getCurrentTurn() + ($action == 'startTurn' ? 1 : 0);
    $actionArgs = json_encode($args);

    self::DbQuery(
      "INSERT INTO log (`turn`, `player_id`, `card_id`, `action`, `action_arg`) VALUES ('$turn', '$playerId', '$cardId', '$action', '$actionArgs')"
    );
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
    self::insert($player->getId(), $card->getId(), 'cardPlayed', $args);
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
    $player = is_null($pId) ? '' : "AND `player_id` = '$pId'";
    $offset = $offset ?? 0;
    $actionsNames = "'" . implode("','", $actions) . "'";
    return self::getObjectListFromDb(
      "SELECT * FROM log WHERE `action` IN ($actionsNames) $player AND `turn` = (SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) - $offset ORDER BY log_id DESC"
    );
  }

  public static function getLastAction($action, $pId = null, $offset = null)
  {
    $actions = self::getLastActions([$action], $pId, $offset);
    return count($actions) > 0 ? json_decode($actions[0]['action_arg'], true) : null;
  }

  public static function getActionsAfter($action, $lastAction)
  {
    $sql = "SELECT action_arg FROM log WHERE action='$action' AND log_id > (SELECT IFNULL(MAX(log_id), 0) FROM log WHERE action='$lastAction')";
    $res = self::getObjectListFromDB($sql);
    return array_values(
      array_map(function ($row) {
        return json_decode($row['action_arg'], true);
      }, $res)
    );
  }

  public static function getPlayerTurn()
  {
    $turn = self::getObjectFromDb("SELECT * FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1");
    return is_null($turn) ? null : $turn['player_id'];
  }

  public static function getCurrentCard()
  {
    $action = self::getObjectFromDb(
      "SELECT * FROM log WHERE `action` = 'cardPlayed' AND `turn` = (SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) ORDER BY log_id DESC LIMIT 1"
    );
    return is_null($action) ? null : $action['card_id'];
  }

  /*
  public static function getReactPlayers()
  {
    $args = self::getLastAction("react");
    if(!isset($args['_private'])) return [];
    $players = array_keys($args['_private']);
    return count($players) > 1? $players : $players[0];
  }
*/
}
