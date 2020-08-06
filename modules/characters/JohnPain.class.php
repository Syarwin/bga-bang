<?php

class JohnPain extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = JOHN_PAIN;
    $this->character_name = clienttranslate('John Pain');
    $this->text  = [
      clienttranslate("If he has less than 6 cards in hand, each time any player draws!, John adds the card just drawn to his hand."),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}