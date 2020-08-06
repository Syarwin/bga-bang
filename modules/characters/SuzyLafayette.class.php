<?php

class SuzyLafayette extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = SUZY_LAFAYETTE;
    $this->character_name = clienttranslate('Suzy Lafayette');
    $this->text  = [
      clienttranslate("As soon as she has no cards in her hand, she draws a card from the draw pile. "),

    ];
    $this->bullets = 4;  
    parent::__construct($row);
  }
}