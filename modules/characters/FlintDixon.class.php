<?php

class FlintDixon extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = FLINT_DIXON;
    $this->character_name = clienttranslate('Flint Dixon');
    $this->text  = [
      clienttranslate("Target must discard BANG! or take hit"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}