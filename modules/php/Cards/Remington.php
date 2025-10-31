<?php

namespace BANG\Cards;

use BANG\Models\WeaponCard;

class Remington extends WeaponCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_REMINGTON;
    $this->name = clienttranslate('Remington');
    $this->text = clienttranslate('Range: 3');
    $this->symbols = [[SYMBOL_RANGE3]];
    $this->copies = [
      BASE_GAME => ['KC'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 3,
    ];
  }
}
