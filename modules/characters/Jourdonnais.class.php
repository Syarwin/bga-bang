<?php

class Jourdonnais extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = JOURDONNAIS;
    $this->character_name = clienttranslate('Jourdonnais');
    $this->text  = [
      clienttranslate("Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed."),

    ];
    $this->bullets = 4;  
  }
}