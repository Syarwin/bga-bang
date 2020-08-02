<?php

class JesseJones extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JESSE_JONES;
    $this->name  = clienttranslate('Jesse Jones');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player. "),

    ];
    $this->bullets = 4;  
  }
}