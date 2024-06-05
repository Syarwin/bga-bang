<?php
namespace BANG\Cards\Events;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

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
   * @param Player $player
   * @return void
   */
  public function resolveEffect($player = null)
  {
    if ($player->getId() === Globals::getEliminatedFirstPId()) {
      $stack = [ST_PRE_PHASE_ONE, ST_PHASE_ONE_SETUP, ST_PLAY_CARD, ST_DISCARD_EXCESS, ST_END_OF_TURN];
      Stack::setup($stack);
      $player->resurrect(2);
      Notifications::gainedLife($player, 2);
      Notifications::updateDistances();
      $player->drawCards(2);
    }
  }

  /**
   * @param Player $player
   * @return boolean
   */
  public function isResurrectionEffect($player = null)
  {
    if (is_null($player)) return true;
    $eliminatedFirstPId = Globals::getEliminatedFirstPId();
    return $eliminatedFirstPId === 0 || $player->getId() === $eliminatedFirstPId;
  }
}
