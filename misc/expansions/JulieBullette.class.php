<?php

class JulieBullette extends Player {
  public function __construct($row = null)
  {
    $this->character    = JULIE_BULLETTE;
    $this->character_name = clienttranslate('Julie Bullette');
    $this->text  = [
      clienttranslate("Draws 1 card; diamond=may show card and draw another"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}