<?php

class CardBang extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_BANG;
    $this->name  = clienttranslate('BANG!');
    $this->text  = "A Bang to a player in range. Can usually only be played once per turn";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => BASIC_ATTACK, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					'range' => 0,
					'impacts' => INRANGE // NONE, INRANGE, SPECIFIC_RANGE, ALL_OTHER, ALL, ANY
					];



    $this->copies = [
      BASE_GAME => [ 'AS', '8D', '9D', '10D', 'JD', 'QD', 'KD', 'AD', '2C', '3C', 'QH', 'KH', 'AH', '2D', '3D', '4D', '5D', '6D', '7D', '4C', '5C', '6C', '7C', '8C', '9C' ],
      DODGE_CITY => [ '8S', '5C', '6C', 'KC'],
    ];
  }
}