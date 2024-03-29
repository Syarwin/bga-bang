<?php

class ClaustheSaint  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = CLAUS_THE_SAINT;
    $this->character_name = clienttranslate('Claus the Saint');
    $this->text  = [
      clienttranslate("He draws one more card than the number of players, keeps 2 for himself, then gives 1 to each player. "),

    ];
    $this->bullets = 3;
    $this->expansion = BULLET;  
    parent::__construct($row);
  }
}