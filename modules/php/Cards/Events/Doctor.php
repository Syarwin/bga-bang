<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Doctor extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_DOCTOR;
    $this->name = clienttranslate('Doctor');
    $this->text = clienttranslate('When this card enters play, the player(s) with the lowest life points gain 1 life point');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {

  }
}
