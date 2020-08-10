<?php

class LauraBillion extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = LAURA_BILLION;
    $this->character_name = clienttranslate('Laura Billion');
    $this->text  = [
      clienttranslate("“Draw!�? Royals=all brown, green, and orange cards used during turn are redrawn at beginning of next turn"),

    ];
    $this->bullets = 3;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}