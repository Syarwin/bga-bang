<?php

class CrazyWolf extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = CRAZY_WOLF;
    $this->character_name = clienttranslate('Crazy Wolf');
    $this->text  = [
      clienttranslate("Add value of discarded card and attack card; if ≥14, Missed!; ≥18, Dodge; ≥21, Missed! and a BANG! at the attacker"),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}