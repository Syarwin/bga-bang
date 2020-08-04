<?php

class Jourdonnais extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = JOURDONNAIS;
    $this->name = clienttranslate('Jourdonnais');
    $this->text  = [
      clienttranslate("Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed."),

    ];
    $this->bullets = 4;  
  }
}