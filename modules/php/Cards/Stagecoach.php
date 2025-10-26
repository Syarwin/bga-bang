<?php

namespace BANG\Cards;

use BANG\Models\BrownCard;

class Stagecoach extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_STAGECOACH;
    $this->name = clienttranslate('Stagecoach!');
    $this->text = clienttranslate('Draw 2 cards.');
    $this->symbols = [[SYMBOL_DRAW, SYMBOL_DRAW]];
    $this->copies = [
      BASE_GAME => ['9S', '9S'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => DRAW,
      'amount' => 2,
      'impacts' => NONE,
    ];
  }
}
