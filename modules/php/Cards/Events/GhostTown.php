<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class GhostTown extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_GHOST_TOWN;
    $this->name = clienttranslate('Ghost Town');
    $this->text = clienttranslate('Dead players enter play with 0 life points and 3 cards on their turn, and die when their turn ends');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect()
  {

  }
}
