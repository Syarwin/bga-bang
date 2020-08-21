<?php

class BigSpencer extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = BIG_SPENCER;
    $this->character_name = clienttranslate('Big Spencer');
    $this->text  = [
      clienttranslate("He starts with 5 cards. He can't play Missed!. "),

    ];
    $this->bullets = 9;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}