<?php

class JesseJones extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = JESSE_JONES;
    $this->name = clienttranslate('Jesse Jones');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player. "),

    ];
    $this->bullets = 4;  
  }
}