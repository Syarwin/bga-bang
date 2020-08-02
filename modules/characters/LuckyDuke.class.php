<?php

class LuckyDuke extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = LUCKY_DUKE;
    $this->name  = clienttranslate('Lucky Duke');
    $this->text  = [
      clienttranslate("Each time he is required to “draw!,” he flips the top two cards from the deck, and chooses the result he prefers."),
      clienttranslate("Discard both cards afterwards.")
    ];
    $this->bullets = 4;
  }
}
