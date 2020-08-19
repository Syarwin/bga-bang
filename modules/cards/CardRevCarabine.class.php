<?php

class CardRevCarabine extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_REV_CARABINE;
    $this->name  = clienttranslate('Rev. Carabine');
    $this->text  = clienttranslate("Range: 4");
    $this->symbols = [
      [SYMBOL_RANGE4]
    ];
    $this->copies = [
      BASE_GAME => [ 'AC' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => WEAPON,
      'range' => 4,
		];
  }
}
