<?php

class JackWest extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = JACK_WEST;
    $this->character_name = clienttranslate('Jack West');
    $this->text  = [
      clienttranslate("May “draw!�? Spades=target must play another missed or take hit (repeatable)"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}