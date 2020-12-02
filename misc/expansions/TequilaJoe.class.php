<?php

class TequilaJoe extends Player {
  public function __construct($row = null)
  {
    $this->character    = TEQUILA_JOE;
    $this->character_name = clienttranslate('Tequila Joe');
    $this->text  = [
      clienttranslate("Each time he plays a Beer, he regains 2 life points instead of 1. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}