<?php

class RoseDoolan extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ROSE_DOOLAN;
    $this->name  = clienttranslate('Rose Doolan');
    $this->text  = [
      clienttranslate("She sees all players at distance -1"),

    ];
    $this->bullets = 4;  
  }
}