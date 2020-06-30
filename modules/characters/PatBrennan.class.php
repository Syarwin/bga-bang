<?php

class PatBrennan extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PAT_BRENNAN;
    $this->name  = clienttranslate('Pat Brennan');
    $this->text  = [
      clienttranslate("He may draw only one card in play in front of any one player."),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}