<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Blessing extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_BLESSING;
    $this->name = clienttranslate('Blessing');
    $this->text = clienttranslate('All cards are considered Hearts');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function getColorOverride()
  {
    return 'H';
  }
}
