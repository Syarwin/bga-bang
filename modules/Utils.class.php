<?php

abstract class Utils extends APP_GameClass
{
  public static function filter(&$data, $filter)
  {
    $data = array_values(array_filter($data, $filter));
  }

  public static function die($arg = [])
  {
    throw new BgaVisibleSystemException(json_encode($arg));
  }
}
