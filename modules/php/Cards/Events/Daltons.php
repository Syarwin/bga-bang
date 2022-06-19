<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Daltons extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_DALTONS;
    $this->name = clienttranslate('The Daltons');
    $this->text = clienttranslate('When this card enters play, all players with a blue card in play discard one of them');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {
    // TODO: Add new state to stack forcing to discard a blue card
  }
}
