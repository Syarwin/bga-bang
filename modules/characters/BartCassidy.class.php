<?php

class BartCassidy extends BangCharacter {
  public function __construct()
  {
    parent::__construct();
    $this->id    = BART_CASSIDY;
    $this->name  = clienttranslate('Bart Cassidy');
    $this->text  = [
      clienttranslate("Each time he loses a life point, he immediately draws a card from the deck. "),

    ];
    $this->bullets = 4;  
  }
}