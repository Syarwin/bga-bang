<?php

class LeeVanKliff extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = LEE_VAN_KLIFF;
    $this->name  = clienttranslate('Lee Van Kliff');
    $this->text  = [
      clienttranslate("During his turn, he may discard a BANG! to repeat the effect of a brown-bordered card he just played. "),

    ];
    $this->bullets = 4;
    $this->expansion = WILD_WEST_SHOW;  
  }
}