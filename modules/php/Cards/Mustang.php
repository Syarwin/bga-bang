<?php
namespace BANG\Cards;

class Mustang extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
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
