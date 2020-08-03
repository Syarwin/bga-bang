<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{
  protected $game;
  protected $playerId;

  protected $id;
  protected $name;
  protected $text;
  protected $expansion = BASE_GAME;
  protected $implemented = false;
  protected $bullets;

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
