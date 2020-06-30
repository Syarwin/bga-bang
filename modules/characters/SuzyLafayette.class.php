<?php

class SuzyLafayette extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SUZY_LAFAYETTE;
    $this->name  = clienttranslate('Suzy Lafayette');
    $this->text  = [
      clienttranslate("As soon as she has no cards in her hand, she draws a card from the draw pile. "),

    ];
    $this->bullets = 4;  
  }
}