<?php

class CardStagecoach extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_STAGECOACH;
    $this->name  = clienttranslate('Stagecoach!');
    $this->text  = clienttranslate("Draw 2 cards.");
    $this->color = BROWN;
    $this->effect = [
      'type' => DRAW,
			'amount' => 2,
			'impacts' => NONE
		];
    $this->symbols = [
      [SYMBOL_DRAW, SYMBOL_DRAW]
    ];
    $this->copies = [
      BASE_GAME => [ '9D', '9D' ],
      DODGE_CITY => [ ],
    ];
  }
}
