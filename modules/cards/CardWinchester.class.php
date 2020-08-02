<?php

class CardWinchester extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_WINCHESTER;
    $this->name  = clienttranslate('Winchester');
    $this->text  = "Range: 5";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 30;
    $this->effect = ['range' => 5,
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '8S' ],
      DODGE_CITY => [ ],
    ];
  }
}

