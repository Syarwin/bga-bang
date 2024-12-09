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
    $this->text = clienttranslate('You cannot play cards in front of you or any other player.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @return boolean
   */
  public function isCanPlayBlueGreenCards()
  {
    return false;
  }
}
