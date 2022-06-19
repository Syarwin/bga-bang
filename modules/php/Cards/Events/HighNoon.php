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
    $this->text = clienttranslate('At the beginning of each player\'s turn, he loses 1 life point. This must be always the last card, and stays in play until the game ends');
    $this->effect = EFFECT_STARTOFTURN;
    $this->lastCard = true;
    $this->expansion = HIGH_NOON;
  }
}
