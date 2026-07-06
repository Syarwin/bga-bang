<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\BlueCard;

class Mustang extends BlueCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_MUSTANG;
    $this->name = clienttranslate('Mustang');
    $this->text = clienttranslate('Others view you at distance +1');
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['8H', '9H'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = ['type' => RANGE_INCREASE];
  }
}
