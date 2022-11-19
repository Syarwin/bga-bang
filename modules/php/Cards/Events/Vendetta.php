<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Vendetta extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_VENDETTA;
    $this->name = clienttranslate('Vendetta');
    $this->text = clienttranslate('Players "Draw!" at the end of their turn; on a Heart, they play an additional turn (but does not "Draw!" again).');
    $this->effect = EFFECT_ENDOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
