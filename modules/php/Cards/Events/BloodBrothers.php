<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class BloodBrothers extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_BLOOD_BROTHERS;
    $this->name = clienttranslate('Blood Brothers');
    $this->text = clienttranslate('Each player may choose to lose one of his life points to give to another player at the beginning of his turn.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
