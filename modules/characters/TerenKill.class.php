<?php

class TerenKill extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TEREN_KILL;
    $this->name  = clienttranslate('Teren Kill');
    $this->text  = [
      clienttranslate("Each time he would be eliminated 'draw!': if it is not Spades, Teren stays at 1 life point, and draws 1 card. "),

    ];
    $this->bullets = 3;
    $this->expansion = WILD_WEST_SHOW;  
  }
}