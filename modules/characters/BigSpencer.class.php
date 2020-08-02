<?php

class BigSpencer extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = BIG_SPENCER;
    $this->name  = clienttranslate('Big Spencer');
    $this->text  = [
      clienttranslate("He starts with 5 cards. He can't play Missed!. "),

    ];
    $this->bullets = 9;
    $this->expansion = WILD_WEST_SHOW;  
  }
}