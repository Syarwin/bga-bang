<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Helpers\GameOptions;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Rules;
use banghighnoon;

trait TurnTrait
{
  /*
   * stNextPlayer: go to next player
   */
  public function stNextPlayer()
  {
    $activeEvent = EventCards::getActive();
    $activePlayer = Players::getActive();
    $pId = $activeEvent && $activeEvent->nextPlayerCounterClockwise() ?
      Players::getPreviousId($activePlayer, true) :
      Players::getNextId($activePlayer, true);
    $this->gamestate->changeActivePlayer($pId);


    if (Players::get($pId)->isEliminated()) {
      $this->stNextPlayer();
      return;
    }

    if(Players::get($pId)->isZombie()){
      Players::get($pId)->eliminate();
      $this->stNextPlayer();
      return;
    }

    self::giveExtraTime($pId);
    $this->gamestate->nextState('start');
  }

  /*
   * stStartOfTurn: called at the beginning of each player turn
   */
  public function stStartOfTurn()
  {
    Log::startTurn();
    $player = Players::getActive();
    $isSheriff = $player->getRole() === SHERIFF;
    $roundNumber = Globals::getRoundNumber();
    if ($isSheriff) {
      $roundNumber++;
      Globals::setRoundNumber($roundNumber);
    }

    $eventCard = EventCards::getActive();
    $nextEventCard = EventCards::getNext();
    // TODO: we call this method twice if it's Sheriff's 2+ turn, this should be fixed (check setNewTurnRules usages)
    Rules::setNewTurnRules($player, $eventCard);
    $stack = [ST_PRE_PHASE_ONE, ST_PHASE_ONE_SETUP, ST_PLAY_CARD, ST_DISCARD_EXCESS, ST_END_OF_TURN];
    if (GameOptions::isEvents()) {
      array_unshift($stack, ST_RESOLVE_EVENT_EFFECT);
      if ($player->getRole() === SHERIFF && $nextEventCard && $roundNumber > 1) {
        array_unshift($stack, ST_NEW_EVENT);
      }

      if ($player->isUnconscious() && (!$eventCard || !$eventCard->isResurrectionEffect())) {
        $stack = [ST_END_OF_TURN];
      }
    }
    Stack::setup($stack);
    Stack::finishState();
  }

  public function stResolveStack()
  {
  }

  /*****************************************
   **** endOfTurn / discardExcess state ****
   ****************************************/
  public function actEndTurn()
  {
    Stack::unsuspendNext(ST_PLAY_CARD);
    Stack::finishState();
  }

  public function stDiscardExcess()
  {
    Stack::unsuspendNext(ST_DISCARD_EXCESS);
    $player = Players::getActive();
//    if ($player->countHand() <= $player->getHp()) {
      Stack::finishState();
//    }
  }

  public function argDiscardExcess()
  {
    $player = Players::getActive();
    return [
      'amount' => $player->countHand() - $player->getHp(),
      '_private' => [
        'active' => $player->getHand()->toArray(),
      ],
    ];
  }

  public function actCancelEndTurn()
  {
    Stack::suspendCtx();
    Stack::insertOnTop(Stack::newAtom(ST_PLAY_CARD, [
      'pId' => Players::getActiveId(),
      'suspended' => true,
    ]));
    Stack::finishState();
  }

  public function actDiscardExcess($cardIds)
  {
    $cards = Cards::getMany($cardIds);
    Cards::discardMany($cardIds);
    $player = Players::getActive();
    Notifications::discardedCards($player, $cards, false, $cardIds);
    Stack::finishState();
  }

  /*
   * stEndOfTurn: called at the end of each player turn
   */
  public function stEndOfTurn()
  {
    // To make sure we will switch to next player after this one.
    // We had a bug when Suzy Lafayette was drawing a card and "capturing" active player status while real active player was dying
    $ctx = Stack::getCtx();
    if ($ctx['pId']) {
      banghighnoon::get()->gamestate->changeActivePlayer(Rules::getCurrentPlayerId());
    }
    $this->gamestate->nextState('next');
  }
}
