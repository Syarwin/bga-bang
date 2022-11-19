<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Judge extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_JUDGE;
    $this->name = clienttranslate('The Judge');
    $this->text = clienttranslate('Players cannot play cards in front of themselves (i.e. Green or Blue cards). Cards that are already placed in front of players will not be affected.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
