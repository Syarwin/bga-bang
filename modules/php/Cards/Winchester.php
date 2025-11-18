<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\WeaponCard;

class Winchester extends WeaponCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_WINCHESTER;
    $this->name = clienttranslate('Winchester');
    $this->text = clienttranslate('Range: 5');
    $this->symbols = [[SYMBOL_RANGE5]];
    $this->copies = [
      BASE_GAME => ['8S'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 5,
    ];
  }
}
