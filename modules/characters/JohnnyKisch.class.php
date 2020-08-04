<?php

class JohnnyKisch extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = JOHNNY_KISCH;
    $this->name = clienttranslate('Johnny Kisch');
    $this->text  = [
      clienttranslate("Each time he puts a card into play, all other cards in play with the same name are discarded. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
  }
}