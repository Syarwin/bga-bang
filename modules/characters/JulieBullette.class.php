<?php

class JulieBullette extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JULIE_BULLETTE;
    $this->name  = clienttranslate('Julie Bullette');
    $this->text  = [
      clienttranslate("Draws 1 card; diamond=may show card and draw another"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}