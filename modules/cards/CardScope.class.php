<?php

class CardScope extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_SCOPE;
    $this->name  = clienttranslate('Scope');
    $this->text  = "You view others at distance -1";
    $this->color = BLUE; //BROWN, BLUE, GREEN
    $this->effect = ['type' => RANGE_DECREASE, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					];



    $this->copies = [
      BASE_GAME => [ 'AS'],
      DODGE_CITY => [ ],
    ];
  }
}