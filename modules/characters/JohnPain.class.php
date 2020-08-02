<?php

class JohnPain extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JOHN_PAIN;
    $this->name  = clienttranslate('John Pain');
    $this->text  = [
      clienttranslate("If he has less than 6 cards in hand, each time any player draws!, John adds the card just drawn to his hand."),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}