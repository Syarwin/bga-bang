<?php

class CardVolcanic extends BangCard {
  public function __construct()
  {
    parent::__construct();
    $this->id    = CARD_VOLCANIC;
    $this->name  = clienttranslate('Volcanic');
    $this->text  = "Range: 1<br>You can play any number of BANG!";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 30;
    $this->effect = ['range' => 1,
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '10S', '10C' ],
      DODGE_CITY => [ ],
    ];
  }
}

