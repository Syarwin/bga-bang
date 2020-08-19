<?php

class CardStagecoach extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_STAGECOACH;
    $this->name  = clienttranslate('Stagecoach!');
    $this->text  = clienttranslate("Draw 2 cards.");
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_DRAW]
    ];
    $this->copies = [
      BASE_GAME => [ '9D', '9D' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => DRAW,
			'amount' => 2,
			'impacts' => NONE
		];
  }
}
