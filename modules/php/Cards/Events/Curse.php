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
    $this->text = clienttranslate('The suit of all cards is Spades for the whole round');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  /**
   * {@inheritDoc}
   */
  public function getSuitOverride()
  {
    return 'S';
  }
}
