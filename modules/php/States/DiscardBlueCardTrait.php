<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;

trait DiscardBlueCardTrait
{
  public function argChooseAndDiscardBlueCard()
  {
    return [
      'amount' => 1,
      '_private' => [
        'active' => [
          'cards' => Players::getActive()->getBlueCardsInPlay()->toArray(),
          ]
      ],
    ];
  }

  public function stDiscardBlueCard()
  {
    $activePlayer = Players::getActive();
    $blueCards = $activePlayer->getBlueCardsInPlay();
    if ($blueCards->count() > 1) {
      $atom = Stack::newSimpleAtom(ST_CHOOSE_AND_DISCARD_BLUE_CARD, $activePlayer->getId());
      Stack::insertOnTop($atom);
    } else {
      $activePlayer->discardCard($blueCards->first());
    }
    Stack::finishState();
  }

  public function actDiscardBlue($cardId)
  {
    self::checkAction('actDiscardBlue');

    $cardIds = array_map(function ($card) {
      return $card->getId();
    }, $this->argChooseAndDiscardBlueCard()['_private']['active']['cards']);
    if (!in_array($cardId, $cardIds)) {
      throw new \BgaVisibleSystemException('You cannot discard this card!');
    }

    $card = Cards::get($cardId);
    $player = Players::getActive();
    $player->discardCard($card);

    Stack::finishState();
  }
}
