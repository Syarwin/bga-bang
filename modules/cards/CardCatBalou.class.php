<?php

class CardCatBalou extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_CAT_BALOU;
    $this->name  = clienttranslate('Cat Balou');
    $this->text  = clienttranslate("Chosen player discards a card of your choice.");
    $this->color = BROWN;
    $this->effect = [
      'type' => DISCARD,
			'amount' => 1,
			'impacts' => ANY
		];
    $this->symbols = [
      [SYMBOL_DISCARD, SYMBOL_ANY]
    ];
    $this->copies = [
      BASE_GAME => [  'KH', '9D', '10D', 'JD' ],
      DODGE_CITY => [ ],
    ];
  }
}
