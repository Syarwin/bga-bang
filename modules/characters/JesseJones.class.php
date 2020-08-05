<?php

class JesseJones extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = JESSE_JONES;
    $this->character_name = clienttranslate('Jesse Jones');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player. "),

    ];
    $this->bullets = 4;  
  }
}