<?php

class PorterRockwell  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = PORTER_ROCKWELL;
    $this->character_name = clienttranslate('Porter Rockwell');
    $this->text  = [
      clienttranslate("Plays/discards BANG! that can reach any other player"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}