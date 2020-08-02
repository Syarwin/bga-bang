<?php

class LauraBillion extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = LAURA_BILLION;
    $this->name  = clienttranslate('Laura Billion');
    $this->text  = [
      clienttranslate("“Draw!” Royals=all brown, green, and orange cards used during turn are redrawn at beginning of next turn"),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
  }
}