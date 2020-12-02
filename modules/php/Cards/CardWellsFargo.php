<?php
namespace Bang\Cards;

class CardWellsFargo extends BrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_WELLS_FARGO;
    $this->name  = clienttranslate('Wells Fargo');
    $this->text  = clienttranslate("Draw 3 cards.");
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_DRAW, SYMBOL_DRAW]
    ];
    $this->copies = [
      BASE_GAME => [ '3H' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => DRAW,
			'amount' => 3,
			'impacts' => NONE
		];
  }
}
