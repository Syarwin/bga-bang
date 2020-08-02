<?php

class SeanMallory extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = SEAN_MALLORY;
    $this->name  = clienttranslate('Sean Mallory');
    $this->text  = [
      clienttranslate("He may hold in his hand up to 10 cards. "),

    ];
    $this->bullets = 3;
    $this->expansion = DODGE_CITY;  
  }
}