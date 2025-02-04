<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;
use BANG\Managers\Rules;
use bang;
use BANG\Models\AbstractCard;
use BANG\Models\Player;

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
    $player = Players::getActive();
    $this->checkForMustPlayCard($card, $player);
    $player->playCard($card, $args);
    self::giveExtraTime($player->getId());

    Stack::finishState();
  }

  public function checkForMustPlayCard(AbstractCard $card, Player $player)
  {
    $mustPlayCardId = Globals::getMustPlayCardId();
    if (Globals::getIsMustPlayCard() && $mustPlayCardId !== 0) {
      if ($card->getId() === $mustPlayCardId) {
        Globals::setMustPlayCardId(0);
        Globals::setIsMustPlayCard(false);
      } else {
        $cardType = $card->getType();
        $mustPlayCardType = Cards::get($mustPlayCardId)->getType();
        if ($player->getCharacter() === CALAMITY_JANET) {
          if ($cardType === CARD_MISSED) {
            $cardType = CARD_BANG;
          }
          if ($mustPlayCardType === CARD_MISSED) {
            $mustPlayCardType = CARD_BANG;
          }
        }
        if ($cardType === $mustPlayCardType) {
          throw new \BgaUserException(
            bang::get()->totranslate(
              'You must play the highlighted card first because of the Law Of The West event'
            )
          );
        }
        // Technically this should be out of "if ($mustPlayCardId !== 0)". However, I really don't want to add an
        // unused state each time any card played. Currently, is used for Law Of The West only. If any more
        // EFFECT_BEFORE_EACH_PLAY_CARD will be added - consider to move it out of the if
        Stack::insertOnTop(Stack::newSimpleAtom(ST_RESOLVE_BEFORE_PLAY_CARD_EFFECT, $player));
      }
    }
  }
}
