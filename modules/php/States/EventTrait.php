<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\EventCards;
use BANG\Managers\Players;

trait EventTrait
{
  /*
   * stNewEvent: Activate new event and add resolveEffect atom if needed
   */
  public function stNewEvent()
  {
    $ctx = Stack::getCtx();
    $eventCard = EventCards::next();
    $effect = $eventCard->getEffect();
    if ($effect === EFFECT_INSTANT || $effect === EFFECT_STARTOFTURN) {
      Stack::insertOnTop(Stack::newSimpleAtom(ST_RESOLVE_EVENT_EFFECT, $ctx['pId']));
    };
    Stack::finishState();
  }

  /*
   * stNewEvent: Resolves event effect
   */
  public function stResolveEventEffect()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $eventCard = EventCards::getActive();
    $eventCard->resolveEffect($player);
    Stack::finishState();
  }
}
