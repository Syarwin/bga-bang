<?php

class PedroRamirez extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = PEDRO_RAMIREZ;
    $this->name  = clienttranslate('Pedro Ramirez');
    $this->text  = [
      clienttranslate("During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck. "),

    ];
    $this->bullets = 4;  
  }
}