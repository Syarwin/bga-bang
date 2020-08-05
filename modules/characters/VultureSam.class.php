<?php

class VultureSam extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = VULTURE_SAM;
    $this->character_name = clienttranslate('Vulture Sam');
    $this->text  = [
      clienttranslate("Whenever a character is eliminated from the play, he takes all the cards of that player."),

    ];
    $this->bullets = 4;  
  }
}