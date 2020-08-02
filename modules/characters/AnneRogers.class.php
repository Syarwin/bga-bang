<?php

class AnneRogers extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = ANNE_ROGERS;
    $this->name  = clienttranslate('Anne Rogers');
    $this->text  = [
      clienttranslate("Can select blue card to copy its benefit until next turn"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}