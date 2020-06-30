<?php

class UncleWill extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = UNCLE_WILL;
    $this->name  = clienttranslate('Uncle Will');
    $this->text  = [
      clienttranslate("Once during his turn, he may play any card from hand as a General Store. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
  }
}