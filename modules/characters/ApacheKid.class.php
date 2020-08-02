<?php

class ApacheKid extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = APACHE_KID;
    $this->name  = clienttranslate('Apache Kid');
    $this->text  = [
      clienttranslate("Cards of Diamonds played by other players do not affect him. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}