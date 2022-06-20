<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class TrainArrival extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_TRAIN_ARRIVAL;
    $this->name = clienttranslate('Train Arrival');
    $this->text = clienttranslate('Players draw 1 extra card at the start of their turn');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {

  }
}
