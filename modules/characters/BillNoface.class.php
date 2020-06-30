<?php

class BillNoface extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = BILL_NOFACE;
    $this->name  = clienttranslate('Bill Noface');
    $this->text  = [
      clienttranslate("He draws 1 card, plus 1 card for each wound he has. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}