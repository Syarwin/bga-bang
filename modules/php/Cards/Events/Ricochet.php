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
    $this->text = clienttranslate('Each player may discard BANG! cards against cards in play in front of any player: each card is discarded if its owner does not play a Missed! for each one.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function isAimingCards()
  {
      return true;
  }
}
