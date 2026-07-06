<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\BrownCard;

class Panic extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_PANIC;
    $this->name = clienttranslate('Panic!');
    $this->text = clienttranslate('Draw 1 card from a player within range 1.');
    $this->symbols = [[SYMBOL_DRAW, SYMBOL_RANGE1]];
    $this->copies = [
      BASE_GAME => ['JH', 'QH', 'AH', '8D'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => DRAW,
      'amount' => 1,
      'range' => 1,
      'impacts' => SPECIFIC_RANGE,
    ];
  }
}
