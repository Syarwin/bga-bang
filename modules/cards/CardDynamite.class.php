<?php

class CardDynamite extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_DYNAMITE;
    $this->name  = clienttranslate('Dynamite');
    $this->text  = clienttranslate("At the start of your turn reveal top card from the deck. If it's spikes 2-9, you lose 3 life points. Else pass the Dynamite to the player on your left.");
    $this->color = BLUE;
    $this->effect = ['type' => STARTOFTURN];
    $this->symbols = [
      [SYMBOL_DYNAMITE, clienttranslate("Lose 3 life points. Else pass the Dynamite on your left.")]
    ];
    $this->copies = [
      BASE_GAME => [ '2H' ],
      DODGE_CITY => [ ],
    ];
  }
}
