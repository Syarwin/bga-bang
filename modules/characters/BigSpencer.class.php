<?php

class BigSpencer extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = BIG_SPENCER;
    $this->name = clienttranslate('Big Spencer');
    $this->text  = [
      clienttranslate("He starts with 5 cards. He can't play Missed!. "),

    ];
    $this->bullets = 9;
    $this->expansion = WILD_WEST_SHOW;  
  }
}