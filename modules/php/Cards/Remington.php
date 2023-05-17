<?php
namespace BANG\Cards;

class Remington extends \BANG\Models\WeaponCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_REMINGTON;
    $this->name = clienttranslate('Remington');
    $this->text = clienttranslate('Range: 3');
    $this->symbols = [[SYMBOL_RANGE3]];
    $this->copies = [
      BASE_GAME => ['KC'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 3,
    ];
  }
}
