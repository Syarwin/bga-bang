<?php

class ElGringo extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = EL_GRINGO;
    $this->name  = clienttranslate('El Gringo');
    $this->text  = [
      clienttranslate("Each time he loses a life point due to a card played by another player, he draws a random card from the hands of that player "),

    ];
    $this->bullets = 3;  
  }
}