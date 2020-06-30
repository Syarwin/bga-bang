<?php

class JoseDelgado extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = JOSE_DELGADO;
    $this->name  = clienttranslate('José Delgado');
    $this->text  = [
      clienttranslate("Twice in his turn, he may discard a blue card from the hand to draw 2 cards."),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}