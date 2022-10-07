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
    $nextEventCard = EventCards::getNext();
    if ($nextEventCard) {
      Notifications::newEvent($eventCard, $nextEventCard);
    }
    if ($eventCard->getEffect() === EFFECT_INSTANT) {
      $eventCard->resolveEffect($player);
    }
    Rules::setNewTurnRules($player, $eventCard);
    Stack::finishState();
  }

  /*
   * stResolveEventEffect: Resolves event effect
   */
  public function stResolveEventEffect()
  {
    $eventCard = EventCards::getActive();
    if ($eventCard->getEffect() === EFFECT_STARTOFTURN) {
      $ctx = Stack::getCtx();
      $player = Players::get($ctx['pId']);
      $eventCard->resolveEffect($player);
    }
    Stack::finishState();
  }
}