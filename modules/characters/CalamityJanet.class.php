<?php

class CalamityJanet extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = CALAMITY_JANET;
    $this->name = clienttranslate('Calamity Janet');
    $this->text  = [
      clienttranslate("She can play BANG! cards as Missed! cards and vice versa. "),

    ];
    $this->bullets = 4;  
  }
}