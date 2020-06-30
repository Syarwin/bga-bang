<?php

class QueenAnne extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = QUEEN_ANNE;
    $this->name  = clienttranslate('Queen Anne');
    $this->text  = [
      clienttranslate("Discard card from a player seen within distance 2"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}