<?php

class FlintDixon  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = FLINT_DIXON;
    $this->character_name = clienttranslate('Flint Dixon');
    $this->text  = [
      clienttranslate("Target must discard BANG! or take hit"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}