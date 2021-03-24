<?php

class HerbHunter  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = HERB_HUNTER;
    $this->character_name = clienttranslate('Herb Hunter');
    $this->text  = [
      clienttranslate("Each time another player is eliminated, he draws 2 extra cards. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}