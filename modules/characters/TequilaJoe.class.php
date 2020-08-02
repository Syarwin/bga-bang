<?php

class TequilaJoe extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = TEQUILA_JOE;
    $this->name  = clienttranslate('Tequila Joe');
    $this->text  = [
      clienttranslate("Each time he plays a Beer, he regains 2 life points instead of 1. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}