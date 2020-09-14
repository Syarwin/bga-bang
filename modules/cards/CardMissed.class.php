<?php

class CardMissed extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_MISSED;
    $this->name  = clienttranslate('Missed');
    $this->text  = clienttranslate("Discard to avoid an attack");
    $this->symbols = [
      [SYMBOL_MISSED]
    ];
    $this->copies = [
      BASE_GAME => [ '10C','JC','QC','KC','AC','2S','3S','4S','5S','6S','7S','8S' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => DEFENSIVE];
  }

  // can only happen when played as BANG by Calamity Janet
  public function react($card, $player) {
    $bang = new CardBang();
    $bang->react($card, $player);
  }
}
