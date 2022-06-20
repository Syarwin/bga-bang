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
    $this->text = clienttranslate('Turn order direction is reversed, i.e. counter-clockwise');
    $this->effect = EFFECT_ENDOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {

  }
}
