<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Sniper extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_SNIPER;
    $this->name = clienttranslate('Sniper');
    $this->text = clienttranslate('During his turn, the player may discard 2 BANG! cards together against a player: this counts as a BANG! but may be cancelled only by 2 Missed!.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @return boolean
   */
  public function isBangCouldBePlayedWithAnotherBang()
  {
    return true;
  }
}
