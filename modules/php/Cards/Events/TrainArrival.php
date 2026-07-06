<?php

declare(strict_types=1);

namespace BANG\Cards\Events;

use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class TrainArrival extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_TRAIN_ARRIVAL;
    $this->name = clienttranslate('Train Arrival');
    $this->text = clienttranslate('Each player draws one extra card at the end of phase 1 of his turn');
    $this->effect = EFFECT_PHASE_ONE;
    $this->expansion = HIGH_NOON;
  }

  public function getPhaseOneAmountOfCardsToDraw(Player $player): int
  {
    return $player->defaultCardsToDraw() + 1;
  }
}
