<?php

class CardStagecoach extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_STAGECOACH;
    $this->name  = clienttranslate('Stagecoach!');
    $this->text  = "Draw 2 cards.";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => DRAW, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'amount' => 2,
					'impacts' => NONE // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '9D', '9D' ],
      DODGE_CITY => [ ],
    ];
  }
}