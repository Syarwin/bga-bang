<?php

class CardRevCarabine extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_REV_CARABINE;
    $this->name  = clienttranslate('Rev. Carabine');
    $this->text  = "Range: 4";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 30;
    $this->effect = ['range' => 4,
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'AC' ],
      DODGE_CITY => [ ],
    ];
  }
}

