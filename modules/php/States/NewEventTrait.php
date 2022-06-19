<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\EventCards;

trait NewEventTrait
{
  /*
   * stNewEvent: Activate new event and resolve effect if needed
   */
  public function stNewEvent()
  {
    $eventCard = EventCards::next();
    if ($eventCard->getEffect() === EFFECT_INSTANT) {
      $eventCard->resolveEffect();
    }
    Stack::finishState();
  }
}
