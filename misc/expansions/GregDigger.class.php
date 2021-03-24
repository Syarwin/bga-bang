<?php

class GregDigger  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = GREG_DIGGER;
    $this->character_name = clienttranslate('Greg Digger');
    $this->text  = [
      clienttranslate("Each time another player is eliminated, he regains 2 life points. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}