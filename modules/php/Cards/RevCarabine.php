<?php
namespace BANG\Cards;

class RevCarabine extends \BANG\Models\WeaponCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_REV_CARABINE;
    $this->name = clienttranslate('Rev. Carabine');
    $this->text = clienttranslate('Range: 4');
    $this->symbols = [[SYMBOL_RANGE4]];
    $this->copies = [
      BASE_GAME => ['AC'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 4,
    ];
  }
}
