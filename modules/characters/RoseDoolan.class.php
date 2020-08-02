<?php

class RoseDoolan extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = ROSE_DOOLAN;
    $this->name  = clienttranslate('Rose Doolan');
    $this->text  = [
      clienttranslate("She sees all players at distance -1"),

    ];
    $this->bullets = 4;  
  }
}