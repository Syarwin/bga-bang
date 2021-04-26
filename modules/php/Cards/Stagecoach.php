<?php
namespace BANG\Cards;

class Stagecoach extends \BANG\Models\BrownCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_STAGECOACH;
    $this->name = clienttranslate('Stagecoach!');
    $this->text = clienttranslate('Draw 2 cards.');
    $this->symbols = [[SYMBOL_DRAW, SYMBOL_DRAW]];
    $this->copies = [
      BASE_GAME => ['9S', '9S'],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => DRAW,
      'amount' => 2,
      'impacts' => NONE,
    ];
  }
}
