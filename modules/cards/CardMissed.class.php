<?php

class CardMissed extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_MISSED;
    $this->name  = clienttranslate('Missed');
    $this->text  = clienttranslate("Discard to avoid an attack");
    $this->color = BROWN;
    $this->effect = ['type' => DEFENSIVE];
    $this->symbols = [
      [SYMBOL_MISSED]
    ];
    $this->copies = [
      BASE_GAME => [ '10C','JC','QC','KC','AC','2S','3S','4S','5S','6S','7S','8S' ],
      DODGE_CITY => [ ],
    ];
  }
}
