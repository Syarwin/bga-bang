<?php

class LelaDevere extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = LELA_DEVERE;
    $this->name  = clienttranslate('Lela Devere');
    $this->text  = [
      clienttranslate("Steals card that that player has in play of from that player's hand"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}