<?php
namespace BANG\Cards\Events;
use BANG\Core\Notifications;
use BANG\Models\AbstractEventCard;

class Hangover extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_HANGOVER;
    $this->name = clienttranslate('Hangover');
    $this->text = clienttranslate('All characters lose their special abilities');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function isAbilityAvailable()
  {
    return false;
  }
}
