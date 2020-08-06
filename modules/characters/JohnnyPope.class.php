<?php

class JohnnyPope extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = JOHNNY_POPE;
    $this->character_name = clienttranslate('Johnny Pope');
    $this->text  = [
      clienttranslate("Gives LP to other player OR makes player “draw!�? Reds=steal card from player; Blacks=player must discard 2 missed or take hit."),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}