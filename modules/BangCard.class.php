<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
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
  private $copies = [];
  private $color;
  private $implemented = false;

  public isPlayable() { return false; }
}
