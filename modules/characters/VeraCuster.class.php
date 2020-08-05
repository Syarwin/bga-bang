<?php

class VeraCuster extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = VERA_CUSTER;
    $this->character_name = clienttranslate('Vera Custer');
    $this->text  = [
      clienttranslate("For one whole round, she gains the same ability of another character in play of her choice. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}