<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class HighNoon extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_HIGH_NOON;
    $this->name = clienttranslate('High Noon');
    $this->text = clienttranslate('Each player loses 1 life point at the start of his turn');
    $this->effect = EFFECT_STARTOFTURN;
    $this->lastCard = true;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect($player = null)
  {
    $player->loseLife();
  }
}
