<?php

class CardVolcanic extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_VOLCANIC;
    $this->name  = clienttranslate('Volcanic');
    $this->text  = clienttranslate("Range: 1. You can play any number of BANG!");
    $this->color = BLUE;
    $this->effect = [
      'type' => WEAPON,
      'range' => 1,
		];
    $this->symbols = [
      [clienttranslate("You can play any number of BANG!")],
      [SYMBOL_RANGE1]
    ];
    $this->copies = [
      BASE_GAME => [ '10S', '10C' ],
      DODGE_CITY => [ ],
    ];
  }
}
