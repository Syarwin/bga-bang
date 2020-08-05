<?php

class LeeVanKliff extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = LEE_VAN_KLIFF;
    $this->character_name = clienttranslate('Lee Van Kliff');
    $this->text  = [
      clienttranslate("During his turn, he may discard a BANG! to repeat the effect of a brown-bordered card he just played. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}