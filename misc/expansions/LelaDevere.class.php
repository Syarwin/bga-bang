<?php

class LelaDevere extends Player {
  public function __construct($row = null)
  {
    $this->character    = LELA_DEVERE;
    $this->character_name = clienttranslate('Lela Devere');
    $this->text  = [
      clienttranslate("Steals card that that player has in play of from that player's hand"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}