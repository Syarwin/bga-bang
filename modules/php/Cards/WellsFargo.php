<?php

namespace BANG\Cards;

use BANG\Models\BrownCard;

class WellsFargo extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_WELLS_FARGO;
    $this->name = clienttranslate('Wells Fargo');
    $this->text = clienttranslate('Draw 3 cards.');
    $this->symbols = [[SYMBOL_DRAW, SYMBOL_DRAW, SYMBOL_DRAW]];
    $this->copies = [
      BASE_GAME => ['3H'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => DRAW,
      'amount' => 3,
      'impacts' => NONE,
    ];
  }
}
