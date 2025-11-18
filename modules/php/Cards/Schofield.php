<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\WeaponCard;

class Schofield extends WeaponCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_SCHOFIELD;
    $this->name = clienttranslate('Schofield');
    $this->text = clienttranslate('Range: 2');
    $this->symbols = [[SYMBOL_RANGE2]];
    $this->copies = [
      BASE_GAME => ['JC', 'QC', 'KS'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 2,
    ];
  }
}
