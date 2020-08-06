<?php

class MaggieMae extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = MAGGIE_MAE;
    $this->character_name = clienttranslate('Maggie Mae');
    $this->text  = [
      clienttranslate("Take 1 card of that player's cards in play"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}