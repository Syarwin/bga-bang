<?php

class GregDigger extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = GREG_DIGGER;
    $this->name  = clienttranslate('Greg Digger');
    $this->text  = [
      clienttranslate("Each time another player is eliminated, he regains 2 life points. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}