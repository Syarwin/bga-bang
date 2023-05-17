<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Sermon extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SERMON;
    $this->name = clienttranslate('The Sermon');
    $this->text = clienttranslate('Each player cannot use BANG! cards during his turn');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function getBangsAmount()
  {
    return 0;
  }

  public function isBangStrictlyForbidden()
  {
    return true;
  }
}
