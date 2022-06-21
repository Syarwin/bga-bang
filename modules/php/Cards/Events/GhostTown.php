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
    $this->text = clienttranslate('During their turn, eliminated players return to the game as ghosts. They draw 3 cards instead of 2, and cannot die. At the end of their turn, they are eliminated again');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect($player = null)
  {

  }
}
