<?php

class CardJail extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_JAIL;
    $this->name  = clienttranslate('Jail');
    $this->text  = clienttranslate("Equip any player with this. At the start of that players turn reveal top card from the deck. If it''s not heart that player is skipped. Either way, the jail is discarded.");
    $this->color = BLUE;
    $this->effect = ['type' => STARTOFTURN];
    $this->symbols = [
      [SYMBOL_DRAW_HEART, clienttranslate("Discard tha Jail, play normally. Else discard the Jail and skip your turn.")]
    ];
    $this->copies = [
      BASE_GAME => [ 'JS', '4H', '10S' ],
      DODGE_CITY => [ ],
    ];
  }
}
