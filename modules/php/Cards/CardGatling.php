<?php
namespace Bang\Cards;

class CardGatling extends BrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_GATLING;
    $this->name  = clienttranslate('Gatling');
    $this->text  = clienttranslate("A Bang to al other players");
    $this->symbols = [
      [SYMBOL_BANG, SYMBOL_OTHER]
    ];
    $this->copies = [
      BASE_GAME => [ '10H' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => BASIC_ATTACK,
			'range' => 0,
			'impacts' => ALL_OTHER
		];
  }
}
