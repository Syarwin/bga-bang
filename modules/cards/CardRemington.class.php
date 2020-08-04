<?php

class CardRemington extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_REMINGTON;
    $this->name  = clienttranslate('Remington');
    $this->text  = "Range: 3";
    $this->color = BLUE; //BROWN, BLUE, GREEN
    $this->effect = [
      'type' => WEAPON,
      'range' => 3,
					];



    $this->copies = [
      BASE_GAME => [ 'KC' ],
      DODGE_CITY => [ ],
    ];
  }
}
