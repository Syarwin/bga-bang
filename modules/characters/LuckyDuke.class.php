<?php

class LuckyDuke extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text  = [
      clienttranslate("Each time he is required to “draw!,�? he flips the top two cards from the deck, and chooses the result he prefers."),
      clienttranslate("Discard both cards afterwards.")
    ];
    $this->bullets = 4;
  }
}