<?php

class YoulGrinner extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = YOUL_GRINNER;
    $this->character_name = clienttranslate('Youl Grinner');
    $this->text  = [
      clienttranslate("Before drawing, players with more hand cards than him must give him one card of their choice. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}