<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Reverend extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_REVEREND;
    $this->name = clienttranslate('The Reverend');
    $this->text = clienttranslate('Players cannot play any Beer cards for the whole round');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function isBeerAvailable()
  {
    return false;
  }
}
