<?php

class BlackJack extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = BLACK_JACK;
    $this->name = clienttranslate('Black Jack');
    $this->text  = [
      clienttranslate("during phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card "),

    ];
    $this->bullets = 4;  
  }
}