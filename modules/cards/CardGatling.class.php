<?php

class CardGatling extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_GATLING;
    $this->name  = clienttranslate('Gatling');
    $this->text  = clienttranslate("A Bang to al other players");
    $this->color = BROWN;
    $this->effect = [
      'type' => BASIC_ATTACK,
			'range' => 0,
			'impacts' => ALL_OTHER
		];
    $this->symbols = [
      [SYMBOL_BANG, SYMBOL_OTHER]
    ];
    $this->copies = [
      BASE_GAME => [ '10H' ],
      DODGE_CITY => [ ],
    ];
  }
}
