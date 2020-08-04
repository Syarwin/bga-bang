<?php

class BelleStar extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = BELLE_STAR;
    $this->name = clienttranslate('Belle Star');
    $this->text  = [
      clienttranslate("During her turn, cards in play in front of other players have no effect. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}