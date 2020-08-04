<?php

class CardPanic extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_PANIC;
    $this->name  = clienttranslate('Panic!');
    $this->text  = "Draw 1 card from the deck or a player within range 1.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => DRAW, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 1,
					'range' => 1,
					'impacts' => SPECIFIC_RANGE // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'JH', 'QH', 'AH', '8D' ],
      DODGE_CITY => [ ],
    ];
  }
}