<?php

class CardSchofield extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_SCHOFIELD;
    $this->name  = clienttranslate('Schofield');
    $this->text  = clienttranslate("Range: 2");
    $this->color = BLUE;
    $this->effect = [
      'type' => WEAPON,
      'range' => 2,
		];
    $this->symbols = [
      [SYMBOL_RANGE2]
    ];
    $this->copies = [
      BASE_GAME => [ 'JC', 'QC', 'KS' ],
      DODGE_CITY => [ ],
    ];
  }
}
