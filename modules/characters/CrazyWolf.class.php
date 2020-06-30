<?php

class CrazyWolf extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CRAZY_WOLF;
    $this->name  = clienttranslate('Crazy Wolf');
    $this->text  = [
      clienttranslate("Add value of discarded card and attack card; if ≥14, Missed!; ≥18, Dodge; ≥21, Missed! and a BANG! at the attacker"),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
  }
}