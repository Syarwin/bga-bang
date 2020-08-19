<?php

class CardRemington extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_REMINGTON;
    $this->name  = clienttranslate('Remington');
    $this->text  = clienttranslate("Range: 3");
    $this->symbols = [
      [SYMBOL_RANGE3]
    ];
    $this->copies = [
      BASE_GAME => [ 'KC' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => WEAPON,
      'range' => 3,
		];
  }
}
