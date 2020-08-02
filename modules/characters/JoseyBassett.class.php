<?php

class JoseyBassett extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JOSEY_BASSETT;
    $this->name  = clienttranslate('Josey Bassett');
    $this->text  = [
      clienttranslate("Draw 2 cards at end of phase 2"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}