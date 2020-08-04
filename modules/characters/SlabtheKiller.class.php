<?php

class SlabtheKiller extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = SLAB_THE_KILLER;
    $this->name = clienttranslate('Slab the Killer');
    $this->text  = [
      clienttranslate("Players trying to cancel his BANG! cards need to play 2 Missed! "),

    ];
    $this->bullets = 4;  
  }
}