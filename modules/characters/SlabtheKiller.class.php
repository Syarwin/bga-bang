<?php

class SlabtheKiller extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = SLAB_THE_KILLER;
    $this->character_name = clienttranslate('Slab the Killer');
    $this->text  = [
      clienttranslate("Players trying to cancel his BANG! cards need to play 2 Missed! "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  function getAmountToCounterBang() { return 2; }
}
