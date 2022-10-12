<?php
namespace BANG\Core;
use banghighnoon;

class Stats
{
  protected static function init($type, $name, $value = 0)
  {
    banghighnoon::get()->initStat($type, $name, $value);
  }

  public static function inc($name, $player = null, $value = 1, $log = true)
  {
    $pId = is_null($player) ? null : ($player instanceof \BANG\Player ? $player->getId() : $player);
    banghighnoon::get()->incStat($value, $name, $pId);
  }

  protected static function get($name, $player = null)
  {
    banghighnoon::get()->getStat($name, $player);
  }

  protected static function set($value, $name, $player = null)
  {
    $pId = is_null($player) ? null : ($player instanceof \WTO\Player ? $player->getId() : $player);
    banghighnoon::get()->setStat($value, $name, $pId);
  }

  public static function setupNewGame()
  {
    /*
    self::init('table', 'turns_number');
    self::init('table', 'ending', 0);

    $stats = banghighnoon::get()->getStatTypes();
    foreach ($stats['player'] as $key => $value) {
      if($value['id'] > 10 && $value['type'] == 'int')
        self::init('player', $key);
    }
    */
  }

  /*
  public static function newTurn(){
    self::set(Globals::getCurrentTurn(), 'turns_number');
  }
*/
}

?>
