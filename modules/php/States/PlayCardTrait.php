<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;
use BANG\Managers\Rules;
use banghighnoon;

trait PlayCardTrait
{
  public function argPlayCards()
  {
    return [
      '_private' => [
        'active' => Players::getActive()->getHandOptions(),
      ],
    ];
  }

  public function stPlayCard()
  {
    $player = Players::getActive();
    if ($player->getHand()->count() == 0) {
      Notifications::tell(clienttranslate('${player_name} does not have any cards in hand and thus ends their turn'), [
        'player_name' => $player->getName(),
      ]);
      Stack::unsuspendNext(ST_PLAY_CARD);
      Stack::finishState();
    }
  }

  public function actPlayCard($cardId, $args)
  {
    self::checkAction('actPlayCard');
    $cardIds = array_map(function ($card) {
      return $card['id'];
    }, $this->argPlayCards()['_private']['active']['cards']);
    if (!in_array($cardId, $cardIds)) {
      throw new \BgaVisibleSystemException('You cannot play this card!');
    }
    if ($args['secondCardId'] && !Rules::isBangCouldBePlayedWithAnotherBang()) {
      throw new \BgaVisibleSystemException('Two cards have been selected but Sniper is not active, please report a bug');
    }

    $card = Cards::get($cardId);
    $mustPlayCardId = Globals::getMustPlayCardId();
    if ($mustPlayCardId !== 0 && $cardId !== $mustPlayCardId) {
      $cardType = $card->getType();
      $mustPlayCardType = Cards::get($mustPlayCardId)->getType();
      if ($cardType === $mustPlayCardType) {
        throw new \BgaUserException(banghighnoon::get()->totranslate('You must play the highlighted card first because of the Law Of The West event'));
      }
    }
    $player = Players::getActive();
    $player->playCard($card, $args);
    self::giveExtraTime($player->getId());

    Stack::finishState();
  }
}
