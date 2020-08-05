<?php

class QueenAnne extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = QUEEN_ANNE;
    $this->character_name = clienttranslate('Queen Anne');
    $this->text  = [
      clienttranslate("Discard card from a player seen within distance 2"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}