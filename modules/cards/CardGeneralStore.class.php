<?php

class CardGeneralStore extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_GENERAL_STORE;
    $this->name  = clienttranslate('General Store');
    $this->text  = clienttranslate("Reveal as many cards as players left. Each player chooses one, starting with you");
    $this->color = BROWN;
    $this->effect = ['type' => STARTOFTURN];
    $this->symbols = [
      [clienttranslate("Reveal as many card as players. Each player draws one.")]
    ];
    $this->copies = [
      BASE_GAME => [ '9C', 'QS' ],
      DODGE_CITY => [ ],
    ];
  }
}
