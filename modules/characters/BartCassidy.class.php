<?php

class BartCassidy extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = BART_CASSIDY;
    $this->character_name = clienttranslate('Bart Cassidy');
    $this->text  = [
      clienttranslate("Each time he loses a life point, he immediately draws a card from the deck. "),

    ];
    $this->bullets = 4;  
  }
}