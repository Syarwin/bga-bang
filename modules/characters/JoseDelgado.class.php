<?php

class JoseDelgado extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = JOSE_DELGADO;
    $this->name = clienttranslate('José Delgado');
    $this->text  = [
      clienttranslate("Twice in his turn, he may discard a blue card from the hand to draw 2 cards."),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}