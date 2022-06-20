<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Shootout extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SHOOTOUT;
    $this->name = clienttranslate('Shootout');
    $this->text = clienttranslate('Players can play one extra "Bang!" on their turns');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }
}
