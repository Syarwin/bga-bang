<?php

namespace BANG\Cards;

use BANG\Models\BrownCard;

class Saloon extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_SALOON;
    $this->name = clienttranslate('Saloon');
    $this->text = clienttranslate('All players regain 1 life point.');
    $this->symbols = [[SYMBOL_LIFEPOINT, SYMBOL_OTHER], [SYMBOL_LIFEPOINT]];
    $this->copies = [
      BASE_GAME => ['5H'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => LIFE_POINT_MODIFIER,
      'amount' => 1,
      'impacts' => ALL,
    ];
  }
}
