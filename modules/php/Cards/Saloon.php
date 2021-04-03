<?php
namespace BANG\Cards;

class Saloon extends \BANG\Models\BrownCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_SALOON;
    $this->name = clienttranslate('Saloon');
    $this->text = clienttranslate('All players regain 1 life point.');
    $this->symbols = [[SYMBOL_LIFEPOINT, SYMBOL_OTHER], [SYMBOL_LIFEPOINT]];
    $this->copies = [
      BASE_GAME => ['5H'],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => LIFE_POINT_MODIFIER,
      'amount' => 1,
      'impacts' => ALL,
    ];
  }
}
