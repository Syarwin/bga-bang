<?php

class CardBarrel extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_BARREL;
    $this->name  = clienttranslate('Barrel');
    $this->text  = "Reveal top card from the deck when you're attacked. If it's a heart it's a miss.";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 21;
    $this->effect = ['type' => DEFENSIVE, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'QS', 'KS' ],
      DODGE_CITY => [ ],
    ];
  }
}

