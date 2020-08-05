<?php

class PorterRockwell extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = PORTER_ROCKWELL;
    $this->character_name = clienttranslate('Porter Rockwell');
    $this->text  = [
      clienttranslate("Plays/discards BANG! that can reach any other player"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}