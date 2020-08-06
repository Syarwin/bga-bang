<?php

class EvaPlace extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = EVA_PLACE;
    $this->character_name = clienttranslate('Eva Place');
    $this->text  = [
      clienttranslate("Card becomes trap; may have 2 out at a time. When acted against, a trap must activate; she chooses which 1."),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}