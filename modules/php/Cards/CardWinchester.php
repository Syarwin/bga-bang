<?php
namespace Bang\Cards;

class CardWinchester extends BlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_WINCHESTER;
    $this->name  = clienttranslate('Winchester');
    $this->text  = clienttranslate("Range: 5");
    $this->symbols = [
      [SYMBOL_RANGE5]
    ];
    $this->copies = [
      BASE_GAME => [ '8S' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => WEAPON,
      'range' => 5,
		];
  }
}
