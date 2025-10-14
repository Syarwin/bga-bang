<?php
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

  /**
   * @param Player $player
   * @return int
   */
  public function getPhaseOneAmountOfCardsToDraw($player)
  {
    return $player->defaultCardsToDraw() + 1;
  }
}
