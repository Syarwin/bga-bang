<?php

class HerbHunter extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = HERB_HUNTER;
    $this->name = clienttranslate('Herb Hunter');
    $this->text  = [
      clienttranslate("Each time another player is eliminated, he draws 2 extra cards. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}