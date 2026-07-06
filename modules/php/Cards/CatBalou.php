<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\BrownCard;

class CatBalou extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_CAT_BALOU;
    $this->name = clienttranslate('Cat Balou');
    $this->text = clienttranslate('Chosen player discards a card of your choice.');
    $this->symbols = [[SYMBOL_DISCARD, SYMBOL_ANY]];
    $this->copies = [
      BASE_GAME => ['KH', '9D', '10D', 'JD'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => DISCARD,
      'amount' => 1,
      'impacts' => ANY,
    ];
  }
}
