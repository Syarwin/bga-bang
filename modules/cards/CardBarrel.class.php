<?php

class CardBarrel extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_BARREL;
    $this->name  = clienttranslate('Barrel');
    $this->text  = clienttranslate("Reveal top card from the deck when you're attacked. If it's a heart it's a miss.");
    $this->color = BLUE;
    $this->effect = ['type' => DEFENSIVE ];
    $this->symbols = [
      [SYMBOL_DRAW_HEART, SYMBOL_MISSED]
    ];
    $this->copies = [
      BASE_GAME => [ 'QS', 'KS' ],
      DODGE_CITY => [ ],
    ];
  }

  public function activate($player) {
    $card = BangCardManager::draw();
    BangNotificationManager::drawCard($player, $card, $this);
    if ($card->format()['color'] == 'H') {
      BangNotificationManager::tell('Barrel blocked the attack');
      return true;
    } else {
      BangNotificationManager::tell('Barrel failed to block the attack');
      return false;
    }
  }
}
