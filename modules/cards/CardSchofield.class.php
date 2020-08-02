<?php

class CardSchofield extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_SCHOFIELD;
    $this->name  = clienttranslate('Schofield');
    $this->text  = "Range: 2";
    $this->color = BLUE; //BROWN, BLUE, GREEN
	$this->type  = 30;
    $this->effect = ['range' => 2,
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ 'JC', 'QC', 'KS' ],
      DODGE_CITY => [ ],
    ];
  }
}

