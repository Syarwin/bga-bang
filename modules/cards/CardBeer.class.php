<?php

class CardBeer extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_BEER;
    $this->name  = clienttranslate('Beer');
    $this->text  = "Regain one life point";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => LIFE_POINT_MODIFIER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 1,
					'impacts' => NONE // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '6H','7H','8H','9H','10H','JH', ],
      DODGE_CITY => [ ],
    ];
  }
}

