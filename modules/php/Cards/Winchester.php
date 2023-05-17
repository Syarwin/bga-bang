<?php
namespace BANG\Cards;

class Winchester extends \BANG\Models\WeaponCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_WINCHESTER;
    $this->name = clienttranslate('Winchester');
    $this->text = clienttranslate('Range: 5');
    $this->symbols = [[SYMBOL_RANGE5]];
    $this->copies = [
      BASE_GAME => ['8S'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 5,
    ];
  }
}
