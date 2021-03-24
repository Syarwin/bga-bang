<?php

class PatBrennan  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = PAT_BRENNAN;
    $this->character_name = clienttranslate('Pat Brennan');
    $this->text  = [
      clienttranslate("He may draw only one card in play in front of any one player. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}