<?php

class CardMustang extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_MUSTANG;
    $this->name  = clienttranslate('Mustang');
    $this->text  = clienttranslate("Others view you at distance +1");
    $this->color = BLUE;
    $this->effect = ['type' => RANGE_INCREASE];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ '8H', '9H'],
      DODGE_CITY => [ ],
    ];
  }
}
