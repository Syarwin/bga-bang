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
    Stack::resolve();
  }

  public function stResolveStack()
  {
  }

  /*****************************************
   **** endOfTurn / discardExcess state ****
   ****************************************/
  public function actEndTurn()
  {
    Stack::nextState();
  }

  public function stDiscardExcess()
  {
    $player = Players::getActive();
    if ($player->countHand() <= $player->getHp()) {
      Stack::nextState();
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
    Stack::insertOnTop([
      'state' => ST_PLAY_CARD,
      'pId' => Players::getActiveId(),
    ]);
    Stack::resolve();
  }

  public function actDiscardExcess($cardIds)
  {
    $cards = Cards::get($cardIds);
    Cards::discardMany($cardIds);
    $player = Players::getActive();
    Notifications::discardedCards($player, $cards);
    Stack::nextState();
  }

  /*
   * stEndOfTurn: called at the end of each player turn
   */
  public function stEndOfTurn()
  {
    $this->gamestate->nextState('next');
  }
}
