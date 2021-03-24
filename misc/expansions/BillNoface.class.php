<?php

class BillNoface  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = BILL_NOFACE;
    $this->character_name = clienttranslate('Bill Noface');
    $this->text  = [
      clienttranslate("He draws 1 card, plus 1 card for each wound he has. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}