<?php

class GreygoryDeck extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = GREYGORY_DECK;
    $this->name  = clienttranslate('Greygory Deck');
    $this->text  = [
      clienttranslate("At the start of his turn, he may draw 2 characters at random. He has all the abilities of the drawn characters. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}