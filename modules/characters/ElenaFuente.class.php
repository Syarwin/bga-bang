<?php

class ElenaFuente extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = ELENA_FUENTE;
    $this->name  = clienttranslate('Elena Fuente');
    $this->text  = [
      clienttranslate("She can use any card as a Missed! "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}