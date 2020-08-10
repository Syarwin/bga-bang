<?php

class CardPanic extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_PANIC;
    $this->name  = clienttranslate('Panic!');
    $this->text  = clienttranslate("Draw 1 card from the deck or a player within range 1.");
    $this->color = BROWN;
    $this->effect = [
      'type' => DRAW,
			'amount' => 1,
			'range' => 1,
			'impacts' => SPECIFIC_RANGE
		];
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_RANGE1]
    ];
    $this->copies = [
      BASE_GAME => [ 'JH', 'QH', 'AH', '8D' ],
      DODGE_CITY => [ ],
    ];
  }
}
