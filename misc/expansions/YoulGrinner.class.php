<?php

class YoulGrinner  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = YOUL_GRINNER;
    $this->character_name = clienttranslate('Youl Grinner');
    $this->text  = [
      clienttranslate("Before drawing, players with more hand cards than him must give him one card of their choice. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}