<?php

class SidKetchum extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = SID_KETCHUM;
    $this->character_name = clienttranslate('Sid Ketchum');
    $this->text  = [
      clienttranslate("He may discard 2 cards to regain 1 life point"),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }
}
