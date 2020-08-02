<?php

class JohnnyKisch extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JOHNNY_KISCH;
    $this->name  = clienttranslate('Johnny Kisch');
    $this->text  = [
      clienttranslate("Each time he puts a card into play, all other cards in play with the same name are discarded. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
  }
}