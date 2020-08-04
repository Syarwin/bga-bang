<?php

class ApacheKid extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = APACHE_KID;
    $this->name = clienttranslate('Apache Kid');
    $this->text  = [
      clienttranslate("Cards of Diamonds played by other players do not affect him. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}