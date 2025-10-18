<?php

namespace BANG\Cards;

use BANG\Models\BlueCard;

class Hideout extends BlueCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_HIDEOUT;
    $this->name = clienttranslate('Hideout');
    $this->text = clienttranslate('Others view you at distance +1');
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => [],
      HIGH_NOON => [],
      DODGE_CITY => ['KD'],
    ];
    $this->effect = ['type' => RANGE_INCREASE];
  }
}
