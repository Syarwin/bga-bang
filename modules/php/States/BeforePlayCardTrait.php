<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\EventCards;
use BANG\Managers\Players;

trait BeforePlayCardTrait
{
  public function stResolveBeforePlayCardEffect()
  {
    $activeEvent = EventCards::getActive();
    if ($activeEvent && $activeEvent->getEffect() === EFFECT_BEFORE_EACH_PLAY_CARD) {
      $activeEvent->resolveEffect(Players::getActive());
    }
    Stack::finishState();
  }
}
