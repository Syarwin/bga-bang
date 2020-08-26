<?php

class DocHolyday extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = DOC_HOLYDAY;
    $this->character_name = clienttranslate('Doc Holyday');
    $this->text  = [
      clienttranslate("During his turn, he may discard once 2 cards from the hand to shoot a BANG! "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}