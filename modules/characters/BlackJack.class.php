<?php

class BlackJack extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = BLACK_JACK;
    $this->character_name = clienttranslate('Black Jack');
    $this->text  = [
      clienttranslate("during phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card "),

    ];
    $this->bullets = 4;  
    parent::__construct($row);
  }
}