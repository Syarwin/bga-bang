<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Core\Stack;

trait RanchTrait
{
  public function argRanch()
  {
    $player = Players::getActive();
    return [
      '_private' => [
        'active' => [ 'cards' => $player->getHand()->toArray() ],
      ],
    ];
  }

  /**
   * @param int[] $cardIds
   * @return void
   */
  public function actDiscardCardsRanch($cardIds)
  {
    self::checkAction('actDiscardCardsRanch');
    $currentPlayer = Players::getCurrent();
    Cards::discardMany($cardIds);
    Notifications::discardedCards($currentPlayer, $cardIds);
    $currentPlayer->drawCards(count($cardIds));
    Stack::finishState();
  }

  public function actIgnoreRanch()
  {
    self::checkAction('actIgnoreRanch');
    Stack::finishState();
  }

}
