<?php
namespace BANG\Core;

/*
 * Stack: a class that handle resolution stack
 */
class Stack extends \APP_GameClass
{
  public static function setup(){
    $arg = json_encode([]);
    self::DbQuery("INSERT INTO stack (`arg`) VALUES ('$arg')");
  }

  public static function get(){
    $arg = self::getUniqueValueFromDB("SELECT arg FROM stack LIMIT 1");
    return json_decode($arg);
  }
}
