<?php

class CardGatling extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_GATLING;
    $this->name  = clienttranslate('Gatling');
    $this->text  = "A Bang to al other players";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => BASIC_ATTACK, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'range' => 0,
					'impacts' => ALL_OTHER // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '10H' ],
      DODGE_CITY => [ ],
    ];
  }
}