<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{
  private $game;
  private $playerId;
  
  private $id;
  private $name;
  private $text;
  private $expansion = BASE_GAME;
  private $implemented = false;
  private $bullets;

  public function __construct($game, $playerId)
  {
    $this->game = $game;
    $this->playerId = $playerId;
  }

  public function getUiData()
	{
		return [
			'id'        => $this->id,
			'name'      => $this->name,
			'text'      => $this->text,
			'bullets'   => $this->bullets,
		];
	}

  public function getBullets(){ return $this->bullets; }
}
