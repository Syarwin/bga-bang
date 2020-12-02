<?php
namespace Bang\Characters;

class WillytheKid extends Player {
  public function __construct($row = null)
  {
    $this->character    = WILLY_THE_KID;
    $this->character_name = clienttranslate('Willy the Kid');
    $this->text  = [
      clienttranslate("He can play any number of BANG! during his turn. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function hasUnlimitedBangs() { return true; }
}
