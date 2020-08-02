<?php

class CardIndians extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_INDIANS;
    $this->name  = clienttranslate('Indians!');
    $this->text  = "All other players discard a BANG! or lose 1 life point.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => OTHER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'range' => 0,
					'impacts' => ALL_OTHER // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'KD', 'AD' ],
      DODGE_CITY => [ ],
    ];
  }
}

