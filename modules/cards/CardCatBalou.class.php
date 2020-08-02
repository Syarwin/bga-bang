<?php

class CardCatBalou extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_CAT_BALOU;
    $this->name  = clienttranslate('Cat Balou');
    $this->text  = "Chosen Player discards a Card of your choice.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => DISCARD, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 1,
					'impacts' => ANY // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [  'KH', '9D', '10D', 'JD' ],
      DODGE_CITY => [ ],
    ];
  }
}

