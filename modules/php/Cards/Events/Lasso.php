<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Lasso extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_LASSO;
    $this->name = clienttranslate('Lasso');
    $this->text = clienttranslate('Cards in play in front of all players have no effect. "Draw!" is still required for "Jail", but this "Jail" has no effect regardless the result of draw.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @return boolean
   */
  public function isIgnoreCardsInPlay()
  {
    return true;
  }
}
