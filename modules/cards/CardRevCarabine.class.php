<?php

class CardRevCarabine extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_REV_CARABINE;
    $this->name  = clienttranslate('Rev. Carabine');
    $this->text  = "Range: 4";
    $this->color = BLUE; //BROWN, BLUE, GREEN
    $this->effect = [
      'type' => WEAPON,
      'range' => 4,
					];



    $this->copies = [
      BASE_GAME => [ 'AC' ],
      DODGE_CITY => [ ],
    ];
  }
}
