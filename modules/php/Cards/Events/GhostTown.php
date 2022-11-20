<?php
namespace BANG\Cards\Events;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Players;
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
    $stack = [ST_PRE_PHASE_ONE, ST_PHASE_ONE_SETUP, ST_PLAY_CARD, ST_END_OF_TURN];
    Stack::setup($stack);
    $atom = Stack::newAtom(ST_PRE_ELIMINATE_DISCARD, [
      'type' => 'eliminate',
      'src' => '',
      'pId' => Players::getActive()->getId(),
      'forceEliminate' => true,
    ]);;
    Stack::insertAfter($atom, 4); // Between ST_PLAY_CARD and ST_END_OF_TURN to ensure death
    $player->resurrect();
    Notifications::updateDistances();
  }

  /**
   * @return int
   */
  public function getPhaseOneAmountOfCardsToDraw()
  {
    return Players::getActive()->getHp() <= 0 ? 3 : 2;
  }

  /**
   * @return boolean
   */
  public function isResurrectionEffect()
  {
    return true;
  }
}
