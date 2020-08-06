<?php

class CalamityJanet extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text  = [
      clienttranslate("She can play BANG! cards as Missed! cards and vice versa. "),

    ];
    $this->bullets = 4;  
    parent::__construct($row);
  }
}