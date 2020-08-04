<?php

class SidKetchum extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = SID_KETCHUM;
    $this->name = clienttranslate('Sid Ketchum');
    $this->text  = [
      clienttranslate("He may discard 2 cards to regain 1 life point"),

    ];
    $this->bullets = 4;  
  }
}