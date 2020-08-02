<?php

class CardGeneralStore extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_GENERAL_STORE;
    $this->name  = clienttranslate('General Store');
    $this->text  = "Reveal as many cards as players left. Each player chooses one, starting with you";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => OTHER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '9C', 'QS' ],
      DODGE_CITY => [ ],
    ];
  }
}

