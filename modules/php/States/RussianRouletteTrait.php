<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;

trait RussianRouletteTrait
{
  public function argRussianRoulette()
  {
    return [
      '_private' => [
        'active' => [
          'cards' => Players::getActive()->getMissedWithOptions(),
          ]
      ],
    ];
  }

  /**
   * @param int $cardId
   */
  public function actReactRussianRoulette($cardId)
  {
    self::checkAction('actReactRussianRoulette');

    $cardIds = array_map(function ($card) {
      return (int) $card['id'];
    }, $this->argRussianRoulette()['_private']['active']['cards']);
    if (!in_array($cardId, $cardIds)) {
      throw new \BgaVisibleSystemException('You cannot discard this card!');
    }

    $card = Cards::get($cardId);
    $player = Players::getActive();
    $player->discardCard($card);

    Stack::finishState();
  }

  /**
   * @param int $cardId
   */
  public function actPassEndRussianRoulette()
  {
    self::checkAction('actPassEndRussianRoulette');

    $player = Players::getActive();
    $player->loseLife(2);
    Stack::removeAllAtomsWithState(ST_RUSSIAN_ROULETTE);
    Stack::resolve();
  }
}
