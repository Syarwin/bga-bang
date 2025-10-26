<?php

namespace BANG\Cards;

use BANG\Models\BlueCard;

class Scope extends BlueCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_SCOPE;
    $this->name = clienttranslate('Scope');
    $this->text = clienttranslate('You view others at distance -1');
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['AS'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = ['type' => RANGE_DECREASE];
  }
}
