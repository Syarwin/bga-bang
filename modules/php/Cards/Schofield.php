<?php
namespace BANG\Cards;

class Schofield extends \BANG\Models\WeaponCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_SCHOFIELD;
    $this->name = clienttranslate('Schofield');
    $this->text = clienttranslate('Range: 2');
    $this->symbols = [[SYMBOL_RANGE2]];
    $this->copies = [
      BASE_GAME => ['JC', 'QC', 'KS'],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 2,
    ];
  }
}
