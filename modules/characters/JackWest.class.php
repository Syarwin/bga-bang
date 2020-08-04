<?php

class JackWest extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = JACK_WEST;
    $this->name = clienttranslate('Jack West');
    $this->text  = [
      clienttranslate("May “draw!�? Spades=target must play another missed or take hit (repeatable)"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}