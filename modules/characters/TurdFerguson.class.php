<?php

class TurdFerguson extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TURD_FERGUSON;
    $this->name  = clienttranslate('Turd Ferguson');
    $this->text  = [
      clienttranslate("Gains 1 free Missed!, used by tapping character card; does not stockpile."),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
  }
}