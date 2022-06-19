<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Core\Stack;

trait TurnTrait
{
  /*
   * stNextPlayer: go to next player
   */
  public function stNextPlayer()
  {
    $pId = $this->activeNextPlayer();

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
   * stStartOfTurn: called at the beggining of each player turn
   */
  public function stStartOfTurn()
  {
    Log::startTurn();
    $player = Players::getActive();
    Globals::setPIdTurn($player->getId());
    Stack::setup([ST_DRAW_CARDS, ST_PLAY_CARD, ST_DISCARD_EXCESS, ST_END_OF_TURN]);
    $player->startOfTurn();
    if ($player->getRole() === SHERIFF) { // TODO: New event should be drawn on Sheriff's second turn. First turn is ok while developing though
      $atom = Stack::newAtom(ST_NEW_EVENT, [
        'pId' => $player->getId(),
      ]);
      Stack::insertOnTop($atom);
    }
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
    if ($player->countHand() <= $player->getHp()) {
      Stack::finishState();
    }
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
    $this->gamestate->nextState('next');
  }
}
