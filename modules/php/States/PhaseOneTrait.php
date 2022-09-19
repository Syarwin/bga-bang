<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Stack;
use BANG\Managers\Rules;

trait PhaseOneTrait
{
  /*
   * stPhaseOneSetup: called on the beginning of each player turn, if the turn was not skipped, to add 3 phase on sub-phases
   */
  public function stPhaseOneSetup()
  {
    $player = Players::getActive();

    Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_CARDS_DRAW_END));
    if (Rules::isPhaseOnePlayerSpecialDraw()) {
      Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_PLAYER_ABILITY_DRAW));
    }
    Stack::insertOnTop(self::phaseOneAtom($player, RULE_PHASE_ONE_CARDS_DRAW_BEGINNING));
    Stack::finishState();
  }

  private function phaseOneAtom($player, $drawCards)
  {
    return Stack::newAtom(ST_PHASE_ONE_DRAW_CARDS, [
      'pId' => $player->getId(),
      'subPhase' => $drawCards
    ]);
  }

  /*
 * stPhaseOneSetup: called on the beginning of each player turn, if the turn was not skipped, to add 3 phase on sub-phases
 */
  public function stPhaseOneDrawCards()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $subPhase = $ctx['subPhase'];
    if ($subPhase === RULE_PHASE_ONE_PLAYER_ABILITY_DRAW) {
      $player->drawCardsAbility();
    } else {
      $amount = Rules::getPhaseOneCardsAmount($subPhase);
      $player->drawCards($amount);
    }
    Stack::finishState();
  }
}
