<?php

class LilSureShot extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = LIL_SURE_SHOT;
    $this->name  = clienttranslate('Lil\' Sure Shot');
    $this->text  = [
      clienttranslate("May play 2 guns; with 1, she can fire a BANG! within its range and a BANG! within the range of the Colt .45; with 2, only 1 BANG! Is needed to fire both."),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}