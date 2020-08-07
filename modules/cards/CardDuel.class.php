<?php

class CardDuel extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_DUEL;
    $this->name  = clienttranslate('Duel');
    $this->text  = clienttranslate("A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.");
    $this->color = BROWN;
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ANY
		];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'QD', 'JS', '8C'],
      DODGE_CITY => [ ],
    ];
  }
}
