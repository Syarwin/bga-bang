<?php

class EvaPlace extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = EVA_PLACE;
    $this->name  = clienttranslate('Eva Place');
    $this->text  = [
      clienttranslate("Card becomes trap; may have 2 out at a time. When acted against, a trap must activate; she chooses which 1."),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
  }
}