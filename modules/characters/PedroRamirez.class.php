<?php

class PedroRamirez extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text  = [
      clienttranslate("During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    if(Utils::getStateName() == 'drawCards') {
      BangLog::addAction("draw", ['deck', 'discard']);
      return 'draw';
    } else {
      return parent::drawCards($amount);
    }
  }

  public function useAbility($args) {
    $cards = [];
    if($args['selected'] == 'deck') {
      $cards = BangCardManager::deal($this->id, 2);
      BangNotificationManager::drawCards($this, $cards);
    } else {
      // Draw the first one from discard
      $cards = BangCardManager::dealFromDiscard($this->id, 1);
      BangNotificationManager::drawCardFromDiscard($this, $cards);
      // The second one from deck
      $cards = BangCardManager::deal($this->id, 1);
      BangNotificationManager::drawCards($this, $cards);
    }
    return "play";
  }

}
