<?php

class CardPanic extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_PANIC;
    $this->name  = clienttranslate('Panic!');
    $this->text  = clienttranslate("Draw 1 card from a player within range 1.");
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_RANGE1]
    ];
    $this->copies = [
      BASE_GAME => [ 'JH', 'QH', 'AH', '8D' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => DRAW,
			'amount' => 1,
			'range' => 1,
			'impacts' => SPECIFIC_RANGE
		];
  }
}
