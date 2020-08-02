<?php

class CardRemington extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_REMINGTON;
    $this->name  = clienttranslate('Remington');
    $this->text  = "Range: 3";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 30;
    $this->effect = ['range' => 3,
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'KC' ],
      DODGE_CITY => [ ],
    ];
  }
}

