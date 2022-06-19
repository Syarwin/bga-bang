<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Curse extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_CURSE;
    $this->name = clienttranslate('Curse');
    $this->text = clienttranslate('All cards are considered Spades');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function getColorOverride()
  {
    return 'S';
  }
}
