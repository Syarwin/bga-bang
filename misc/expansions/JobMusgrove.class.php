<?php

class JobMusgrove  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = JOB_MUSGROVE;
    $this->character_name = clienttranslate('Job Musgrove');
    $this->text  = [
      clienttranslate("May “draw!�? Royals=discard card from attacker's hand"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}