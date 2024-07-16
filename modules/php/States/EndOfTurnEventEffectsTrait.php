<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\EventCards;
use BANG\Managers\Players;

trait EndOfTurnEventEffectsTrait
{
  public function stResolveEndOfTurnEventEffects()
  {
    $activeEvent = EventCards::getActive();
    if ($activeEvent && $activeEvent->getEffect() === EFFECT_END_OF_TURN) {
      $activeEvent->resolveEffect(Players::getActive());
    }
    Stack::finishState();
  }
}
