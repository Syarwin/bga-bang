<?php

class CardJail extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_JAIL;
    $this->name  = clienttranslate('Jail');
    $this->text  = "Equip any player with this. At the start of that players turn reveal top card from the deck. If it''s not heart that player is skipped. Either way, the jail is discarded.";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 22;
    $this->effect = ['type' => OTHER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'JS', '4H', '10S' ],
      DODGE_CITY => [ ],
    ];
  }
}

