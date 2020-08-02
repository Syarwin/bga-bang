<?php

class DocHolyday extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = DOC_HOLYDAY;
    $this->name  = clienttranslate('Doc Holyday');
    $this->text  = [
      clienttranslate("During his turn, he may discard once 2 cards from the hand to shoot a BANG! "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}