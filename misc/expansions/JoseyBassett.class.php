<?php

class JoseyBassett  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = JOSEY_BASSETT;
    $this->character_name = clienttranslate('Josey Bassett');
    $this->text  = [
      clienttranslate("Draw 2 cards at end of phase 2"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}