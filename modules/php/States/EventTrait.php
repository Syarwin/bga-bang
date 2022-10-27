<?php
namespace BANG\States;
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
    // EFFECT_PERMANENT should not logically be here but in case of Hangover + Paul Regret we should notify about distances, so...
    // Feel free to change this logic if at some point EFFECT_INSTANT will trigger anything
    if ($eventCard->getEffect() === EFFECT_INSTANT || $eventCard->getEffect() === EFFECT_PERMANENT) {
      $eventCard->resolveEffect($player);
    }
    Stack::finishState();
  }

  /*
   * stResolveEventEffect: Resolves event effect
   */
  public function stResolveEventEffect()
  {
    $eventCard = EventCards::getActive();
    if ($eventCard && $eventCard->getEffect() === EFFECT_STARTOFTURN) {
      $ctx = Stack::getCtx();
      $player = Players::get($ctx['pId']);
      $eventCard->resolveEffect($player);
    }
    Stack::finishState();
  }
}