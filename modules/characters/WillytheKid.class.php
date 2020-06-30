<?php

class WillytheKid extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = WILLY_THE_KID;
    $this->name  = clienttranslate('Willy the Kid');
    $this->text  = [
      clienttranslate("He can play any number of BANG! during his turn. "),

    ];
    $this->bullets = 4;  
  }
}