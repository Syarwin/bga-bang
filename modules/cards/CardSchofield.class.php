<?php

class CardSchofield extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_SCHOFIELD;
    $this->name  = clienttranslate('Schofield');
    $this->text  = clienttranslate("Range: 2");
    $this->symbols = [
      [SYMBOL_RANGE2]
    ];
    $this->copies = [
      BASE_GAME => [ 'JC', 'QC', 'KS' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => WEAPON,
      'range' => 2,
		];
  }
}
