<?php

class LeeVanKliff extends Player {
  public function __construct($row = null)
  {
    $this->character    = LEE_VAN_KLIFF;
    $this->character_name = clienttranslate('Lee Van Kliff');
    $this->text  = [
      clienttranslate("During his turn, he may discard a BANG! to repeat the effect of a brown-bordered card he just played. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}