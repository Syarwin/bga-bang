<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{

  public function __construct()
  {
  }

  public $name;
  public $text;
  public $expansion = BASE_GAME;
  public $implemented = false;
  public $bullets;
}
