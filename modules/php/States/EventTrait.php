<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Managers\Rules;

trait EventTrait
{
  /*
   * stNewEvent: Activate new event and add resolveEffect atom if needed
   */
  public function stNewEvent()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $eventCard = EventCards::next();
    Notifications::newEvent($eventCard, EventCards::getNext());
    Rules::setNewTurnRules($player, $eventCard);
    // Maybe Suzy has no cards in hand?
    Players::getLivingPlayers()->map(function ($player) {
      $player->checkHand();
    });

    if ($eventCard->getEffect() === EFFECT_INSTANT) {
      $eventCard->resolveEffect($player);
    }
    Notifications::updateDistances();
    if (!EventCards::isResurrectionPossible()) {
      Globals::setResurrectionIsPossible(false);
    }
    Stack::finishState();
  }

  /*
   * stResolveEventEffect: Resolves start-of-turn event effect (not the best naming unfortunately)
   */
  public function stResolveEventEffect()
  {
    $eventCard = EventCards::getActive();
    if ($eventCard && $eventCard->getEffect() === EFFECT_STARTOFTURN) {
      $ctx = Stack::getCtx();
      $player = Players::get($ctx['pId']);
      if ($eventCard->isResurrectionEffect() === $player->isUnconscious()) { // resurrect + dead or normal effect + alive
        $eventCard->resolveEffect($player);
      } elseif ($player->isUnconscious()) { // dead but this is not a resurrection
        Stack::removePlayerAtoms($ctx['pId']);
      } // do not resolve any effects when resurrection is combined with alive player
    }
    Stack::finishState();
  }

  public function actAgreedToDisclaimer() {
    Players::getCurrent()->agreeToDisclaimer();
  }
}