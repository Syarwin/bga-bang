<?php

class PixiePete extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PIXIE_PETE;
    $this->name  = clienttranslate('Pixie Pete');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he draws 3 cards instead of 2."),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}