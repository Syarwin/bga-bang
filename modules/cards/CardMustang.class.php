<?php

class CardMustang extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_MUSTANG;
    $this->name  = clienttranslate('Mustang');
    $this->text  = "Others view you at distance +1";
    $this->color = BLUE; //BROWN, BLUE, GREEN
    $this->effect = ['type' => RANGE_INCREASE, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					];



    $this->copies = [
      BASE_GAME => [ '8H', '9H'],
      DODGE_CITY => [ ],
    ];
  }
}