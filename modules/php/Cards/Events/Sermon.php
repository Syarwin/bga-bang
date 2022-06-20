<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Sermon extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SERMON;
    $this->name = clienttranslate('Sermon');
    $this->text = clienttranslate('Players cannot play "Bang!" on their turns');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }
}
