<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Sniper extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SNIPER;
    $this->name = clienttranslate('Sniper');
    $this->text = clienttranslate('Players may discard 2 "Bang!" cards to target an opponent; this counts as a "Bang!", but 2 "Missed!" cards are required to cancel this effect.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
