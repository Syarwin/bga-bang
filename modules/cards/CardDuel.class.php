<?php

class CardDuel extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_DUEL;
    $this->name  = clienttranslate('Duel');
    $this->text  = "A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
	$this->type  = 12;
    $this->effect = ['type' => OTHER, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'range' => 0,
					'impacts' => ANY // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'QD', 'JS', '8C'],
      DODGE_CITY => [ ],
    ];
  }
}

