<?php

class SidKetchum extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SID_KETCHUM;
    $this->name  = clienttranslate('Sid Ketchum');
    $this->text  = [
      clienttranslate("He may discard 2 cards to regain 1 life point."),

    ];
    $this->bullets = 4;  
  }
}