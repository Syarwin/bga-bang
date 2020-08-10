<?php

class CardScope extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_SCOPE;
    $this->name  = clienttranslate('Scope');
    $this->text  = clienttranslate("You view others at distance -1");
    $this->color = BLUE;
    $this->effect = ['type' => RANGE_DECREASE];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'AS'],
      DODGE_CITY => [ ],
    ];
  }
}
