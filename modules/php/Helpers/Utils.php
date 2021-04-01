<?php
namespace BANG\Helpers;
use bang;

abstract class Utils
{
  public static function filter(&$data, $filter)
  {
    if(\is_array($data)){
      $data = array_values(array_filter($data, $filter));
    } else {
      $data = $data->filter($filter);
    }
  }

  public static function die($args=null){
    if(is_null($args)) throw new \BgaVisibleSystemException(implode("<br>", self::$logmsg));
    throw new \BgaVisibleSystemException(json_encode($args));
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
    return bang::get()->gamestate->state()['name'];
  }

  public function getCopyValue($copy) {
    return substr($copy, 0, -1);
  }
  public function getCopyColor($copy) {
    return substr($copy, -1);
  }
}