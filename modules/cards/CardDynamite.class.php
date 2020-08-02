<?php

class CardDynamite extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_DYNAMITE;
    $this->name  = clienttranslate('Dynamite');
    $this->text  = "At the start of your turn reveal top card from the deck. If it''s Pikes 2-9, you lose 3 life points. Else pass the Dynamite to the player on your left.";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 22;
    $this->effect = ['type' => OTHER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '2H' ],
      DODGE_CITY => [ ],
    ];
  }
}

