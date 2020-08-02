<?php

class Jourdonnais extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = JOURDONNAIS;
    $this->name  = clienttranslate('Jourdonnais');
    $this->text  = [
      clienttranslate("Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed."),

    ];
    $this->bullets = 4;  
  }
}