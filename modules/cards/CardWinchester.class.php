<?php

class CardWinchester extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_WINCHESTER;
    $this->name  = clienttranslate('Winchester');
    $this->text  = clienttranslate("Range: 5");
    $this->color = BLUE;
    $this->effect = [
      'type' => WEAPON,
      'range' => 5,
		];
    $this->symbols = [
      [SYMBOL_RANGE5]
    ];
    $this->copies = [
      BASE_GAME => [ '8S' ],
      DODGE_CITY => [ ],
    ];
  }
}
