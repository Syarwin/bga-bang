<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{
  protected $game;
  protected $playerId;

  public function __construct($game, $playerId)
  {
    $this->game = $game;
    $this->playerId = $playerId;
  }

  private $id;
  private $name;
  private $text;
  protected $expansion = BASE_GAME;
  protected $implemented = false;
  protected $bullets;
}
