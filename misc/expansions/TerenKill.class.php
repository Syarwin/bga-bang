<?php

class TerenKill extends Player {
  public function __construct($row = null)
  {
    $this->character    = TEREN_KILL;
    $this->character_name = clienttranslate('Teren Kill');
    $this->text  = [
      clienttranslate("Each time he would be eliminated 'draw!': if it is not Spades, Teren stays at 1 life point, and draws 1 card. "),

    ];
    $this->bullets = 3;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}