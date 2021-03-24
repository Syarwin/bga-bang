<?php

class BelleStar  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = BELLE_STAR;
    $this->character_name = clienttranslate('Belle Star');
    $this->text  = [
      clienttranslate("During her turn, cards in play in front of other players have no effect. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}