<?php

class CardRevCarabine extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_REV_CARABINE;
    $this->name  = clienttranslate('Rev. Carabine');
    $this->text  = clienttranslate("Range: 4");
    $this->color = BLUE;
    $this->effect = [
      'type' => WEAPON,
      'range' => 4,
		];
    $this->symbols = [
      [SYMBOL_RANGE4]
    ];
    $this->copies = [
      BASE_GAME => [ 'AC' ],
      DODGE_CITY => [ ],
    ];
  }
}
