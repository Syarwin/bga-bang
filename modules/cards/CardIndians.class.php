<?php

class CardIndians extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_INDIANS;
    $this->name  = clienttranslate('Indians!');
    $this->text  = clienttranslate("All other players discard a BANG! or lose 1 life point.");
    $this->color = BROWN;
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ALL_OTHER
		];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'KD', 'AD' ],
      DODGE_CITY => [ ],
    ];
  }
}
