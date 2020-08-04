<?php

class MaggieMae extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = MAGGIE_MAE;
    $this->name = clienttranslate('Maggie Mae');
    $this->text  = [
      clienttranslate("Take 1 card of that player's cards in play"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}