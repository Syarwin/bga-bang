<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class GoldRush extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_GOLD_RUSH;
    $this->name = clienttranslate('Gold Rush');
    $this->text = clienttranslate('The game proceeds counter-clockwise for one whole round, always starting with the Sheriff. All card effects proceed clockwise');
    $this->effect = EFFECT_ENDOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function nextPlayerClockwise()
  {
    return false;
  }
}
