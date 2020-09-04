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

  // TODO : make it work with the dynamite : should lost the three hp THEN draw three cards if not dead
  public function looseLife($amount = 1) {
		$newstate = parent::looseLife($amount);
    if(!$this->eliminated) $this->drawCards($amount);
    return $newstate;
  }
}
