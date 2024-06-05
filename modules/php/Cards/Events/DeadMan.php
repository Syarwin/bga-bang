<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class DeadMan extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_DEAD_MAN;
    $this->name = clienttranslate('Dead Man');
    $this->text = clienttranslate('During his turn, the player who has been eliminated first comes back in play with 2 life points and 2 cards.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @return boolean
   */
  public function isResurrectionEffect()
  {
    return true;
  }
}
