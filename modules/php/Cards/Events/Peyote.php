<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Peyote extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_PEYOTE;
    $this->name = clienttranslate('Peyote');
    $this->text = clienttranslate('Players try to guess the suit of the card they draw and keep drawing until they are wrong.');
    $this->effect = EFFECT_PHASE_ONE;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
