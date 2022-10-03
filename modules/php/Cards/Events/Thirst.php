<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Thirst extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_THIRST;
    $this->name = clienttranslate('Thirst');
    $this->text = clienttranslate('Each player only draws his first card, not the second one, during phase 1 of his turn');
    $this->effect = EFFECT_PHASE_ONE;
    $this->expansion = HIGH_NOON;
  }

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 1;
  }
}
