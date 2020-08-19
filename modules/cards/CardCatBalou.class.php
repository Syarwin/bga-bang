<?php

class CardCatBalou extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_CAT_BALOU;
    $this->name  = clienttranslate('Cat Balou');
    $this->text  = clienttranslate("Chosen player discards a card of your choice.");
    $this->symbols = [
      [SYMBOL_DISCARD, SYMBOL_ANY]
    ];
    $this->copies = [
      BASE_GAME => [  'KH', '9D', '10D', 'JD' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => DISCARD,
			'amount' => 1,
			'impacts' => ANY
		];
  }
}
