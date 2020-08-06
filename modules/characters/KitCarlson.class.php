<?php

class KitCarlson extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = KIT_CARLSON;
    $this->character_name = clienttranslate('Kit Carlson');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down. "),

    ];
    $this->bullets = 4;  
    parent::__construct($row);
  }
}