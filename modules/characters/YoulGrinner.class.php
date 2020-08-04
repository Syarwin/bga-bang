<?php

class YoulGrinner extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = YOUL_GRINNER;
    $this->name = clienttranslate('Youl Grinner');
    $this->text  = [
      clienttranslate("Before drawing, players with more hand cards than him must give him one card of their choice. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}