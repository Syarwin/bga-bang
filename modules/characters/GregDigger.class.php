<?php

class GregDigger extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = GREG_DIGGER;
    $this->character_name = clienttranslate('Greg Digger');
    $this->text  = [
      clienttranslate("Each time another player is eliminated, he regains 2 life points. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}