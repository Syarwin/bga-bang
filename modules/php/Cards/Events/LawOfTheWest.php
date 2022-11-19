<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class LawOfTheWest extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_LAW_OF_THE_WEST;
    $this->name = clienttranslate('Law Of The West');
    $this->text = clienttranslate('Players must show and play (if possible) the second card they draw in their turn.');
    $this->effect = EFFECT_PHASE_ONE;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
