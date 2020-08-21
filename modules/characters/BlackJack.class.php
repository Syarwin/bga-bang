<?php

class BlackJack extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = BLACK_JACK;
    $this->character_name = clienttranslate('Black Jack');
    $this->text  = [
      clienttranslate("during phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    if(Utils::getStateName() == 'drawCards') {
      $cards = BangCardManager::deal($this->id, $amount);
      BangNotificationManager::gainedCards($this, $cards);
      $card = $cards[1];
      BangNotificationManager::tell('Second card was ${card_name}', ['card_name' => $card->getNameAndValue()]);
      $color = $card->getCopyColor();
      if($color == 'H' || $color == 'D') {
        $cards = BangCardManager::deal($this->id, 1);
        BangNotificationManager::gainedCards($this, $cards);
      }
    } else {
      parent::drawCards($amount);
    }
  }

}
