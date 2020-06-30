<?php

class VeraCuster extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = VERA_CUSTER;
    $this->name  = clienttranslate('Vera Custer');
    $this->text  = [
      clienttranslate("For one whole round, she gains the same ability of another character in play of her choice. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}