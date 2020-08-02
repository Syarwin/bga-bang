<?php

class ApacheKid extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = APACHE_KID;
    $this->name  = clienttranslate('Apache Kid');
    $this->text  = [
      clienttranslate("Cards of Diamonds played by other players do not affect him. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}