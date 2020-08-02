<?php

class CardSaloon extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_SALOON;
    $this->name  = clienttranslate('Missed');
    $this->text  = "All players regain 1 life point.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => LIFE_POINT_MODIFIER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 1,
					'impacts' => ALL // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '5H' ],
      DODGE_CITY => [ ],
    ];
  }
}

