<?php

class CardRemington extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_REMINGTON;
    $this->name  = clienttranslate('Remington');
    $this->text  = clienttranslate("Range: 3");
    $this->color = BLUE;
    $this->effect = [
      'type' => WEAPON,
      'range' => 3,
		];
    $this->symbols = [
      [SYMBOL_RANGE3]
    ];
    $this->copies = [
      BASE_GAME => [ 'KC' ],
      DODGE_CITY => [ ],
    ];
  }
}
