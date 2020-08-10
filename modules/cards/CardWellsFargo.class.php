<?php

class CardWellsFargo extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_WELLS_FARGO;
    $this->name  = clienttranslate('Wells Fargo');
    $this->text  = clienttranslate("Draw 3 cards.");
    $this->color = BROWN;
    $this->effect = [
      'type' => DRAW,
			'amount' => 3,
			'impacts' => NONE
		];
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_DRAW, SYMBOL_DRAW]
    ];
    $this->copies = [
      BASE_GAME => [ '3H' ],
      DODGE_CITY => [ ],
    ];
  }
}
