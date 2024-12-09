<?php
namespace BANG\States;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Core\Stack;
use BANG\Managers\Rules;

trait PhaseOneTrait
{

  /*
   * stPrePhaseOne: called before the beginning of each player turn resolving Jail/Dynamite effects
   */
  public function stPrePhaseOne()
  {
    Players::getActive()->startOfTurn();
    Stack::finishState();
  }

  /*
   * stPhaseOneSetup: called on the beginning of each player turn, if the turn was not skipped, to add 3 phase one sub-phases
   */
  public function stPhaseOneSetup()
  {
    $player = Players::getActive();

    $eventCard = EventCards::getActive();
    if ($eventCard && $eventCard->getEffect() === EFFECT_ENDOFPHASEONE) {
      $eventCard->resolveEffect($player);
    }
    Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_CARDS_DRAW_END));
    if (Rules::isPhaseOneEventSpecialDraw()) {
      Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_EVENT_SPECIAL_DRAW));
    }
    if (Rules::isPhaseOnePlayerSpecialDraw()) {
      Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_PLAYER_ABILITY_DRAW,
        ['storeResult' => Rules::isPhaseOneEventSpecialDraw()]));
    }
    Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_CARDS_DRAW_BEGINNING));
    Stack::finishState();
  }

  private function phaseOneAtom($player, $drawCards, $additionalData = [])
  {
    return Stack::newAtom(ST_PHASE_ONE_DRAW_CARDS, array_merge($additionalData, [
      'pId' => $player->getId(),
      'subPhase' => $drawCards
    ]));
  }

  /*
   * stPhaseOneSetup: called on the beginning of each player turn, if the turn was not skipped, we add 3 phase on sub-phases
   */
  public function stPhaseOneDrawCards()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $subPhase = $ctx['subPhase'];
    if ($subPhase === RULE_PHASE_ONE_PLAYER_ABILITY_DRAW) {
      $player->drawCardsPhaseOne();
    } else if ($subPhase === RULE_PHASE_ONE_EVENT_SPECIAL_DRAW) {
      EventCards::getActive()->drawCardsPhaseOne($player);
    } else {
      $amount = Rules::getPhaseOneCardsAmount($subPhase);
      $player->drawCards($amount);
    }
    Stack::finishState();
  }
}
