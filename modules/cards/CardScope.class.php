<?php

class CardScope extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_SCOPE;
    $this->name  = clienttranslate('Scope');
    $this->text  = "You view others at distance -1";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 22;
    $this->effect = ['type' => RANGE_DECREASE, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					];



    $this->copies = [
      BASE_GAME => [ 'AS'],
      DODGE_CITY => [ ],
    ];
  }
}

