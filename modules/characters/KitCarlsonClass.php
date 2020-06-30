<?php

class KitCarlson extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = KIT_CARLSON;
    $this->name  = clienttranslate('Kit Carlson');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down."),

    ];
    $this->bullets = 4;  
  }
}