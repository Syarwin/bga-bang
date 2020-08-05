<?php

class PedroRamirez extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text  = [
      clienttranslate("During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck. "),

    ];
    $this->bullets = 4;  
  }
}