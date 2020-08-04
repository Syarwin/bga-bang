<?php

class PaulRegret extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = PAUL_REGRET;
    $this->name = clienttranslate('Paul Regret');
    $this->text  = [
      clienttranslate("All players see him at a distance +1"),

    ];
    $this->bullets = 3;  
  }
}