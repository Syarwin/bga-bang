<?php

class PatBrennan extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = PAT_BRENNAN;
    $this->name = clienttranslate('Pat Brennan');
    $this->text  = [
      clienttranslate("He may draw only one card in play in front of any one player. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}