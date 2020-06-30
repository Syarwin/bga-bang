<?php

class CardBang extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_BANG;
    $this->name  = clienttranslate('BANG!');
    $this->text  = [ ];
    $this->color = BROWN;
    $this->copies = [
      BASE_GAME => ['2D', ''],
      DODGE_CITY => [],
    ];
  }
}
