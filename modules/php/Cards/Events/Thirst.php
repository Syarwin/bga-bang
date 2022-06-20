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
    $this->text = clienttranslate('Players draw 1 card less at the start of their turn');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {

  }
}
