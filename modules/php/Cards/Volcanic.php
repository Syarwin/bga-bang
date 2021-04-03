<?php
namespace BANG\Cards;

class Volcanic extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_VOLCANIC;
    $this->name = clienttranslate('Volcanic');
    $this->text = clienttranslate('Range: 1. You can play any number of BANG!');
    $this->symbols = [[clienttranslate('You can play any number of BANG!')], [SYMBOL_RANGE1]];
    $this->copies = [
      BASE_GAME => ['10S', '10C'],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => WEAPON,
      'range' => 1,
    ];
  }
}
