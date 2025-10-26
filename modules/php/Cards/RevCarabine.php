<?php

namespace BANG\Cards;

use BANG\Models\WeaponCard;

class RevCarabine extends WeaponCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_REV_CARABINE;
    $this->name = clienttranslate('Rev. Carabine');
    $this->text = clienttranslate('Range: 4');
    $this->symbols = [[SYMBOL_RANGE4]];
    $this->copies = [
      BASE_GAME => ['AC'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 4,
    ];
  }
}
