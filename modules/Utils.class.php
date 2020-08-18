<?php

abstract class Utils extends APP_GameClass
{
  public static function filter(&$data, $filter)
  {
    $data = array_values(array_filter($data, $filter));
  }

  public static function die($args=null){
    if(is_null($args)) throw new BgaVisibleSystemException(implode("<br>", self::$logmsg));
    throw new BgaVisibleSystemException(json_encode($args));
  }

  public static $logmsg = [];

  public static function log($msg) {
    self::$logmsg[] = $msg;
  }

  public static function sort(&$array, $callback) {
    for($i = 1; $i <  count($array); $i++) {
      for($j = 0; $j < $i; $j++) {
        if($callback($array[$i], $array[$j])) {
            $el = array_splice($array, $i,1);
            array_splice($array, $j, 0, $el);
            break;
        }
      }
    }
  }

  public static function getStateName(){
    return bang::$instance->gamestate->state()['name'];
  }
}
