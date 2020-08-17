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

  public function looseLife($byPlayer=null) {
    if(parent::looseLife($byPlayer)) return true;
    $card = BangCardManager::deal($this->getId(), 1);
    BangNotificationManager::gainedCards($player, [$card]);
  }
}
