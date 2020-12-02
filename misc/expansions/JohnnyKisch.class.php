<?php

class JohnnyKisch extends Player {
  public function __construct($row = null)
  {
    $this->character    = JOHNNY_KISCH;
    $this->character_name = clienttranslate('Johnny Kisch');
    $this->text  = [
      clienttranslate("Each time he puts a card into play, all other cards in play with the same name are discarded. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
    parent::__construct($row);
  }
}