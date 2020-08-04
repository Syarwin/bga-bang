<?php

class UncleWill extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = UNCLE_WILL;
    $this->name = clienttranslate('Uncle Will');
    $this->text  = [
      clienttranslate("Once during his turn, he may play any card from hand as a General Store. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
  }
}