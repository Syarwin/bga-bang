<?php

class UncleWill  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = UNCLE_WILL;
    $this->character_name = clienttranslate('Uncle Will');
    $this->text  = [
      clienttranslate("Once during his turn, he may play any card from hand as a General Store. "),

    ];
    $this->bullets = 4;
    $this->expansion = BULLET;  
    parent::__construct($row);
  }
}