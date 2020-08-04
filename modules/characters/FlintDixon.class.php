<?php

class FlintDixon extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = FLINT_DIXON;
    $this->name = clienttranslate('Flint Dixon');
    $this->text  = [
      clienttranslate("Target must discard BANG! or take hit"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}