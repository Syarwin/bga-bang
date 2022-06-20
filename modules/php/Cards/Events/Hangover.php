<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Hangover extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_HANGOVER;
    $this->name = clienttranslate('Hangover');
    $this->text = clienttranslate('Players lose their character ability');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }
}
