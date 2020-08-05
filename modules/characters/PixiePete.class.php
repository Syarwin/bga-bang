<?php

class PixiePete extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = PIXIE_PETE;
    $this->character_name = clienttranslate('Pixie Pete');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he draws 3 cards instead of 2. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}