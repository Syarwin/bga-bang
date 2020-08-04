<?php

class CardWellsFargo extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_WELLS_FARGO;
    $this->name  = clienttranslate('Wells Fargo');
    $this->text  = "Draw 3 cards.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => DRAW, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 3,
					'impacts' => NONE // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '3H' ],
      DODGE_CITY => [ ],
    ];
  }
}