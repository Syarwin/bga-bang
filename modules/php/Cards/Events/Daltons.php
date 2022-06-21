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
    $this->text = clienttranslate('When The Daltons enter play, each player who has any blue cards in front of him, chooses one of them and discard it');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect($player = null)
  {
    // TODO: Add new state to stack forcing to discard a blue card
  }
}
