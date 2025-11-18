<?php

declare(strict_types=1);

namespace BANG\Cards\Events;

use BANG\Models\AbstractEventCard;

class Judge extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_JUDGE;
    $this->name = clienttranslate('The Judge');
    $this->text = clienttranslate('You cannot play cards in front of you or any other player.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function isCanPlayBlueGreenCards(): bool
  {
    return false;
  }
}
