<?php

class FlintWestwood extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = FLINT_WESTWOOD;
    $this->name  = clienttranslate('Flint Westwood');
    $this->text  = [
      clienttranslate("During his turn, he may trade 1 card from hand with 2 cards at random from the hand of another player. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}