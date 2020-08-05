<?php

class FlintWestwood extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = FLINT_WESTWOOD;
    $this->character_name = clienttranslate('Flint Westwood');
    $this->text  = [
      clienttranslate("During his turn, he may trade 1 card from hand with 2 cards at random from the hand of another player. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}