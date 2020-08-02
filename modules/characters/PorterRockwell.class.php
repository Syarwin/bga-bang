<?php

class PorterRockwell extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PORTER_ROCKWELL;
    $this->name  = clienttranslate('Porter Rockwell');
    $this->text  = [
      clienttranslate("Plays/discards BANG! that can reach any other player"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}