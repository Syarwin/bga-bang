<?php

class CardGeneralStore extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_GENERAL_STORE;
    $this->name  = clienttranslate('General Store');
    $this->text  = "Reveal as many cards as players left. Each player chooses one, starting with you";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => STARTOFTURN, 
					];



    $this->copies = [
      BASE_GAME => [ '9C', 'QS' ],
      DODGE_CITY => [ ],
    ];
  }
}
