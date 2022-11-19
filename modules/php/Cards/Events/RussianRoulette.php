<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class RussianRoulette extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RUSSIAN_ROULETTE;
    $this->name = clienttranslate('Russian Roulette');
    $this->text = clienttranslate('When this card enters in play, starting from the Sheriff, each player discards a "Missed!" or loses 2 life points (thus ending the effect).');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
