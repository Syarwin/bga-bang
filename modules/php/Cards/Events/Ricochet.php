<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Ricochet extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RICOCHET;
    $this->name = clienttranslate('Ricochet');
    $this->text = clienttranslate('Players may play "Bang!" against cards in play; those cards are discarded unless the player controlling them plays a "Missed!".');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
