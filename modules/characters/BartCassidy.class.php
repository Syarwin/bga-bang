<?php

class BartCassidy extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = BART_CASSIDY;
    $this->character_name = clienttranslate('Bart Cassidy');
    $this->text  = [
      clienttranslate("Each time he loses a life point, he immediately draws a card from the deck. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function looseLife($attacker = null, $amount = 1) {
		if(parent::looseLife($attacker, $amount)) return true;
    $this->drawCards($amount);
  }
}
