<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Ambush extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_AMBUSH;
    $this->name = clienttranslate('Ambush');
    $this->text = clienttranslate('The distance between players is 1. This is only modified by cards in play.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}