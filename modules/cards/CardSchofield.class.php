<?php

class CardSchofield extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_SCHOFIELD;
    $this->name  = clienttranslate('Schofield');
    $this->text  = "Range: 2";
    $this->color = BLUE; //BROWN, BLUE, GREEN
    $this->effect = [
      'type' => WEAPON,
      'range' => 2,
					];



    $this->copies = [
      BASE_GAME => [ 'JC', 'QC', 'KS' ],
      DODGE_CITY => [ ],
    ];
  }
}
