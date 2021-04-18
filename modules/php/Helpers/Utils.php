<?php
namespace BANG\Helpers;
use bang;

abstract class Utils
{
  public static function filter(&$data, $filter)
  {
    if (\is_array($data)) {
      $data = array_values(array_filter($data, $filter));
    } else {
      $data = $data->filter($filter);
    }
  }

  public static function die($args = null)
  {
    if (is_null($args)) {
      throw new \BgaVisibleSystemException(implode('<br>', self::$logmsg));
    }
    throw new \BgaVisibleSystemException(json_encode($args));
  }

  public static $logmsg = [];

  public static function log($msg)
  {
    self::$logmsg[] = $msg;
  }

  public static function getStateName()
  {
    return bang::get()->gamestate->state()['name'];
  }

  public function getCopyValue($copy)
  {
    return substr($copy, 0, -1);
  }
  public function getCopyColor($copy)
  {
    return substr($copy, -1);
  }

  public static function updateAtomAfterAction($atom, $missedNeeded, $abilityOrCardUsed)
  {
    $atom['missedNeeded'] = $missedNeeded;
    $used = $atom['used'] ?? [];
    array_push($used, $abilityOrCardUsed);
    $atom['used'] = $used;
    return $atom;
  }
}
