<?php

class TurdFerguson extends Player {
  public function __construct($row = null)
  {
    $this->character    = TURD_FERGUSON;
    $this->character_name = clienttranslate('Turd Ferguson');
    $this->text  = [
      clienttranslate("Gains 1 free Missed!, used by tapping character card; does not stockpile."),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}