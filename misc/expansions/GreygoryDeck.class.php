<?php

class GreygoryDeck extends Player {
  public function __construct($row = null)
  {
    $this->character    = GREYGORY_DECK;
    $this->character_name = clienttranslate('Greygory Deck');
    $this->text  = [
      clienttranslate("At the start of his turn, he may draw 2 characters at random. He has all the abilities of the drawn characters. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}