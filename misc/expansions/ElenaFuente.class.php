<?php

class ElenaFuente extends Player {
  public function __construct($row = null)
  {
    $this->character    = ELENA_FUENTE;
    $this->character_name = clienttranslate('Elena Fuente');
    $this->text  = [
      clienttranslate("She can use any card as a Missed! "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}