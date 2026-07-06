<?php

declare(strict_types=1);

namespace BANG\Cards\Events;

use BANG\Models\AbstractEventCard;

class Shootout extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SHOOTOUT;
    $this->name = clienttranslate('Shootout');
    $this->text = clienttranslate('Each player can play a second BANG! card during his turn');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = HIGH_NOON;
  }

  public function getBangsAmount(): int
  {
    return 2;
  }
}
