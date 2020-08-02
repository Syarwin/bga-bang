<?php

class PaulRegret extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = PAUL_REGRET;
    $this->name  = clienttranslate('Paul Regret');
    $this->text  = [
      clienttranslate("All players see him at a distance +1"),

    ];
    $this->bullets = 3;  
  }
}