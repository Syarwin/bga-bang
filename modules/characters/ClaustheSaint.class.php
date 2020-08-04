<?php

class ClaustheSaint extends BangCharacter {
  public function __construct($pid=null, $game=null)
  {
    parent::__construct($pid, $game);
    $this->id    = CLAUS_THE_SAINT;
    $this->name = clienttranslate('Claus the Saint');
    $this->text  = [
      clienttranslate("He draws one more card than the number of players, keeps 2 for himself, then gives 1 to each player. "),

    ];
    $this->bullets = 3;
    $this->expansion = BULLET;  
  }
}