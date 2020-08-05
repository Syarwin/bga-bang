<?php

class PatBrennan extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = PAT_BRENNAN;
    $this->character_name = clienttranslate('Pat Brennan');
    $this->text  = [
      clienttranslate("He may draw only one card in play in front of any one player. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}