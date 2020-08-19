<?php

class CardBarrel extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_BARREL;
    $this->name  = clienttranslate('Barrel');
    $this->text  = clienttranslate("Reveal top card from the deck when you're attacked. If it's a heart it's a miss.");
    $this->symbols = [
      [SYMBOL_DRAW_HEART, SYMBOL_MISSED]
    ];
    $this->copies = [
      BASE_GAME => [ 'QS', 'KS' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => DEFENSIVE ];
  }

  public function activate($player, $args = []) {
    $card = $player->draw($args, $this);
    if(is_null($card)) return; // TODO : can happen ??

    BangCardManager::markAsPlayed($this->id);
    if ($card->getCopyColor() == 'H') {
      BangNotificationManager::tell('Barrel blocked the attack');
      return null;
    } else {
      BangNotificationManager::tell('Barrel failed to block the attack');
      return "updateOptions";
    }
  }
}
