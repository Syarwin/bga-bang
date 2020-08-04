<?php

class ChuckWengam extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = CHUCK_WENGAM;
    $this->name = clienttranslate('Chuck Wengam');
    $this->text  = [
      clienttranslate("During his turn, he may choose to lose 1 life point to draw 2 cards "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}