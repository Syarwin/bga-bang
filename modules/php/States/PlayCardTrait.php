<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;
use BANG\Managers\Rules;

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

  public function argPlayLastCardManually()
  {
    return [
      '_private' => [
        'active' => Players::getActive()->getLastCardWithOptions(),
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

  public function stPlayLastCardAutomatically()
  {
    $activePlayer = Players::getActive();
    $nonActivePlayers = Players::getLivingPlayers($activePlayer->getId());
    $lastCard = Players::getActive()->getLastCardFromHand();
    $lastCardType = $lastCard->getType();

    // TODO: Think on how all those cards could be filtered rather than just listing them here (effect?)
    $specialCardsTypes = [CARD_BANG, CARD_CAT_BALOU, CARD_DUEL, CARD_JAIL, CARD_PANIC, CARD_MISSED];
    if (in_array($lastCardType, $specialCardsTypes) || $lastCard->isWeapon()) {
      if (!$activePlayer->isCardPlayable($lastCard)) {
        // Cannot be played (Missed, Bang with no possible distance)
        $reason = $activePlayer->getNonPlayabilityReason($lastCardType);
        Notifications::showMessageToAll(clienttranslate('${player_name} should have played ${card_name} but this is not possible - ${reason}'), [
          'player' => $activePlayer,
          'card' => $lastCard,
          'reason' => $reason,
        ]);
      } else if ($lastCard->isWeapon()) {
        // Weapon should be just played without args if it's playable
        $activePlayer->playCard($lastCard, []);
      } else if (count($nonActivePlayers) === 1 && in_array($lastCardType, [CARD_BANG, CARD_DUEL])) {
        // It could be played only to a single player left alive, no choice here
        $activePlayer->playCard($lastCard, [
          'type' => 'player',
          'player' => $nonActivePlayers->first()->getId(),
        ]);
      } else {
        // Player must choose the target manually
        $atom = Stack::newSimpleAtom(ST_PLAY_LAST_CARD_MANUALLY, $activePlayer->getId());
        Stack::insertOnTop($atom);
      }
    } else {
      $activePlayer->playCard($lastCard, []);
    }
    Stack::finishState();
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
    $player->playCard($card, $args);
    self::giveExtraTime($player->getId());

    Stack::finishState();
  }
}
