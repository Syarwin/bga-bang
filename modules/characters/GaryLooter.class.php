<?php

class GaryLooter extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = GARY_LOOTER;
    $this->name = clienttranslate('Gary Looter');
    $this->text  = [
      clienttranslate("He draws all excess cards discarded by other players at the end of their turn. "),

    ];
    $this->bullets = 5;
    $this->expansion = WILD_WEST_SHOW;  
  }
}