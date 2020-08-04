<?php

class SuzyLafayette extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = SUZY_LAFAYETTE;
    $this->name = clienttranslate('Suzy Lafayette');
    $this->text  = [
      clienttranslate("As soon as she has no cards in her hand, she draws a card from the draw pile. "),

    ];
    $this->bullets = 4;  
  }
}