<?php

class WillytheKid extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = WILLY_THE_KID;
    $this->character_name = clienttranslate('Willy the Kid');
    $this->text  = [
      clienttranslate("He can play any number of BANG! during his turn. "),

    ];
    $this->bullets = 4;  
  }
}