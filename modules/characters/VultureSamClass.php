<?php

class VultureSam extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = VULTURE_SAM;
    $this->name  = clienttranslate('Vulture Sam');
    $this->text  = [
      clienttranslate("Whenever a character is eliminated from the play, he takes all the cards of that player."),

    ];
    $this->bullets = 4;  
  }
}