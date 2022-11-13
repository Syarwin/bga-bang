<?php
namespace BANG\States;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Core\Stack;
use BANG\Core\Notifications;

trait EndOfLifeTrait
{
  /**
   * BEER saving mode
   */
  public function argReactBeer()
  {
    $player = Players::getActive();

    $needed = 1 - $player->getHp();
    //  TODO auto kill if not enough cards
    //    if (count($hand) >= $needed) {
    return [
      'n' => $needed,
      '_private' => [
        'active' => $player->getBeerOptions(),
      ],
    ];
  }

  public function actReactBeer($ids)
  {
    // Play the beer cards picked by player
    $player = Players::getActive();
    if (empty($ids)) {
      $player->addAtomAfterCardResolution(ST_PRE_ELIMINATE_DISCARD, 'eliminate');
    } else {
      foreach (Cards::getMany($ids) as $card) {
        $player->playCard($card, []);
      }
      $player->addRevivalAtomOrEliminate();
    }
  }

  public function argDiscardEliminate()
  {
    $player = Players::getActive();
    $cards = $player->getCardsInPlay()->merge($player->getHand());
    return [
      'amount' => $cards->count(),
      '_private' => [
        'active' => $cards->toArray(),
      ],
    ];
  }

  public function actDiscardEliminate($cardIds)
  {
    $cards = Cards::getMany($cardIds);
    Cards::discardMany($cardIds);
    $player = Players::getActive();
    Notifications::discardedCards($player, $cards, false, $cardIds);
    $this->gamestate->jumpToState(ST_ELIMINATE);
  }

  public function actDefautDiscardExcess()
  {
    $this->gamestate->jumpToState(ST_ELIMINATE);
  }

  public function stPreEliminateDiscard()
  {
    $player = Players::getActive();
    // Let characters react => mostly Vulture
    foreach (Players::getLivingPlayers($player->getId()) as $opp) {
      $opp->onPlayerPreEliminated($player);
    }

    $cards = $player->getCardsInPlay()->merge($player->getHand());
    $currentEvent = EventCards::getActive();
    $nextIsPedro = Players::getNext($player, $currentEvent && $currentEvent->isResurrectionEffect())->getCharacter() === PEDRO_RAMIREZ;
    if ($cards->count() > 1 && $nextIsPedro) {
      $this->gamestate->jumpToState(ST_PRE_ELIMINATE);
    } else {
      $this->gamestate->jumpToState(ST_ELIMINATE);
    }
  }

  /**
   * Eliminate a player
   */
  public function stEliminate()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $pId = $player->eliminate();
    if ($pId === true) {
      Stack::finishState();
    } else {
      $this->gamestate->changeActivePlayer($pId);
      $this->gamestate->jumpToState(ST_VICE_PENALTY);
    }
  }

  public function actDiscardVicePenalty($cardIds)
  {
    $cards = Cards::getMany($cardIds);
    Cards::discardMany($cardIds);
    $player = Players::getActive();
    Notifications::discardedCards($player, $cards, false, $cardIds);
    Stack::finishState();
  }

  public function actDefautDiscardVicePenalty()
  {
    $player = Players::getActive();
    $player->discardAllCards();
    Stack::finishState();
  }
}
