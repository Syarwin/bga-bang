<?php
namespace BANG\Cards;

class Scope extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_SCOPE;
    $this->name = clienttranslate('Scope');
    $this->text = clienttranslate('You view others at distance -1');
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['AS'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = ['type' => RANGE_DECREASE];
  }
}
