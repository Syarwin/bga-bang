<?php

class JoseDelgado extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = JOSE_DELGADO;
    $this->character_name = clienttranslate('José Delgado');
    $this->text  = [
      clienttranslate("Twice in his turn, he may discard a blue card from the hand to draw 2 cards."),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}