<?php

class BillNoface extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = BILL_NOFACE;
    $this->character_name = clienttranslate('Bill Noface');
    $this->text  = [
      clienttranslate("He draws 1 card, plus 1 card for each wound he has. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}