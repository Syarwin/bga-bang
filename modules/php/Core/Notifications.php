<?php
namespace BANG\Core;
use BANG\Helpers\Collection;
use BANG\Managers\EventCards;
use banghighnoon;
use BANG\Managers\Players;
use BANG\Models\Player;
use BANG\Managers\Cards;
use BANG\Models\AbstractCard;
use BANG\Models\AbstractEventCard;

/*
 * Notifications
 */
class Notifications
{
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
      banghighnoon::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($pId, $name, $msg, $data)
  {
    self::updateArgs($data);
    $pId = is_int($pId) ? $pId : $pId->getId();
      banghighnoon::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function updateHand($player)
  {
    self::notifyAll('updateHand', '', [
      'player' => $player,
      'total' => $player->getHand()->count(),
    ]);
  }

  public static function showMessage($playerId, $message)
  {
    self::notify($playerId, 'showMessage', $message, []);
  }

  public static function showMessageToAll($message, $data = [], $showAsPopup = true)
  {
    $data['showAsPopup'] = $showAsPopup;
    self::notifyAll( 'showMessage', $message, $data);
  }

  /**
   * cardPlayed: called once a card is played
   */
  public static function cardPlayed($player, $card, $args = [])
  {
    $msg = clienttranslate('${player_name} plays ${card_name}');
    $data = [
      'msgYou' => clienttranslate('${You} play ${card_name}'),
      'player' => $player,
      'card' => $card,
      'target' => $card->isEquipment() ? LOCATION_INPLAY : LOCATION_DISCARD,
    ];

    if (isset($args['player'])) {
      $msg = clienttranslate('${player_name} plays ${card_name} and chooses ${player_name2} as target');
      $data['msgYou'] = clienttranslate('${You} play ${card_name} and choose ${player_name2} as target');
      if (isset($args['asBang'])) {
        $msg = clienttranslate('${player_name} plays ${card_name} as BANG! and chooses ${player_name2} as target');
        $data['msgYou'] = clienttranslate('${You} play ${card_name} as BANG! and choose ${player_name2} as target');
      }
      if ($args['type'] === LOCATION_INPLAY) {
        $targetCard = Cards::get($args['arg']);
        $msg = clienttranslate('${player_name} plays ${card_name} and chooses ${player_name2}\'s card ${target_card_name} as a target');
        $data['msgYou'] = clienttranslate('${You} play ${card_name} and choose ${player_name2}\'s card ${target_card_name} as a target');
        $data['target_card'] = $targetCard;
        $data['target_card_name'] = $targetCard->getName();
        $data['i18n'][] = 'target_card_name';
        $data['preserve'][] = $targetCard;
      }

      $data['player2'] = Players::get($args['player']);
    }

    self::notifyAll('cardPlayed', $msg, $data);
  }

  public static function lostLife($player, $amount = 1)
  {
    // TODO: Fix wording "...loses -X life points". Should be "...loses X life points"
    $msg = clienttranslate('${player_name} loses ${amount} life points');
    $msgYou = clienttranslate('${You} lose ${amount} life points');
    if ($amount == 1) {
      $msg = clienttranslate('${player_name} loses a life point');
      $msgYou = clienttranslate('${You} lose a life point');
    }

    self::notifyAll('updateHP', $msg, [
      'player' => $player,
      'hp' => $player->getHp(),
      'amount' => -$amount,
      'msgYou' => $msgYou,
    ]);
  }

  public static function gainedLife($player, $amount)
  {
    $msg = clienttranslate('${player_name} gains ${amount} life points');
    $msgYou = clienttranslate('${You} gain ${amount} life points');
    if ($amount == 1) {
      $msg = clienttranslate('${player_name} gains a life point');
      $msgYou = clienttranslate('${You} gain a life point');
    }

    self::notifyAll('updateHP', $msg, [
      'player' => $player,
      'hp' => $player->getHp(),
      'amount' => $amount,
      'msgYou' => $msgYou,
    ]);
  }

  public static function drawCards($player, $cards, $public = false, $src = LOCATION_DECK, $isMessage = true)
  {
    $amount = $cards->count();
    $data = [
      'i18n' => ['src_name'],
      'src_name' => $src === LOCATION_DECK ? clienttranslate('the deck') : clienttranslate('the discard pile'),
      'player' => $player,
      'amount' => $amount,
      'cards' => $cards->toArray(),
      'src' => $src,
      'target' => LOCATION_HAND,
      'deckCount' => Cards::getDeckCount(),
    ];

    if ($src === LOCATION_DISCARD) {
      $data['nextCard'] = Cards::getTopOf(LOCATION_DISCARD);
    }

    // Notify player
    if ($amount === 1) {
      $msg = $isMessage ? clienttranslate('You draw ${card_name} from ${src_name}') : '';
      $data['card'] = $cards->first();
    } else {
      $msg = clienttranslate('You draw ${amount} cards from ${src_name}');
    }
    self::notify($player, 'cardsGained', $msg, $data);

    // Notify everyone else
    if (!$public) {
      unset($data['card_name']);
      unset($data['card']);
      unset($data['cards']);
      $msg =
        $amount === 1
          ? clienttranslate('${player_name} draws a card from ${src_name}')
          : clienttranslate('${player_name} draws ${amount} cards from ${src_name}');
    } else {
      if ($amount === 1) {
        $msg = $isMessage ? clienttranslate('${player_name} draws ${card_name} from ${src_name}') : '';
      } else {
        $msg = clienttranslate('${player_name} draws ${amount} cards from ${src_name}');
      }
    }
    $data['ignore'] = [$player];
    Notifications::notifyAll('cardsGained', $msg, $data);
  }

  /**
   * @param Player $player
   * @param Collection $cards
   * @param string $isMessage
   * @param string $msgOthersForced
   * @return void
   */
  public static function drawCardFromDiscard($player, $cards, $isMessage = true)
  {
    self::drawCards($player, $cards, true, LOCATION_DISCARD, $isMessage);
  }

  // For general store
  public static function chooseCard($player, $card)
  {
    $msg = clienttranslate('${player_name} chooses ${card_name}');
    Notifications::notifyAll('cardsGained', $msg, [
      'msgYou' => clienttranslate('${You} choose ${card_name}'),
      'player' => $player,
      'card' => $card,
      'amount' => 1,
      'src' => LOCATION_DECK,
      'target' => LOCATION_HAND,
      'deckCount' => Cards::getDeckCount(),
    ]);
  }

  public static function discardedCard($player, $card, $silent = false)
  {
    self::notifyAll('cardLost', '', [
      'player' => $player,
      'card' => $card,
    ]);
    if ($silent) {
      return;
    }

    self::notifyAll('updateHand', clienttranslate('${player_name} discards ${card_name}'), [
      'msgYou' => clienttranslate('${You} discard ${card_name}'),
      'player' => $player,
      'card' => $card,
      'total' => $player->getHand()->count(),
    ]);
  }

  /**
   * @param Player $player
   * @param array $card
   * @return void
   */
  public static function discardedCardToDrawPile($player, $card)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'deckCount' => Cards::getDeckCount(),
    ];
    self::notify($player, 'cardLostToDeck', '', $data);
    self::notify($player, 'updateHand', clienttranslate('${You} discard ${card_name} face down on the deck'), $data);
    $data['ignore'] = [$player];
    unset($data['card']);
    self::notifyAll('cardLostToDeck', '', $data);
  }

  /**
   * @param Player $player
   * @param int[] $cardIds
   * @param boolean $silent
   * @param string $destination
   * @return void
   */
  public static function discardedCards($player, $cardIds, $silent = false, $destination = LOCATION_DISCARD)
  {
    $cards = Cards::getMany($cardIds);

    // We don't use foreach here because we care about the order of discarding
    for ($i = 0; $i < count($cardIds); $i++) {
      $cId = $cardIds[$i];
      $card = $cards[$cId];
      if ($destination === LOCATION_DISCARD) {
        self::discardedCard($player, $card, $silent);
      } else {
        self::discardedCardToDrawPile($player, $card);
      }
    }

    if ($destination === LOCATION_DISCARD) {
      self::notifyAll('updateHand', clienttranslate('${player_name} discards ${amount} card(s) face down on the deck'), [
        'player' => $player,
        'ignore' => [$player],
        'amount' => count($cardIds),
        'total' => $player->getHand()->count(),
      ]);
    }
  }

  public static function stoleCard($receiver, $victim, $card, $equipped)
  {
    $data = [
      'player' => $receiver,
      'player2' => $victim,
      'card' => $card,
      'amount' => 1,
      'src' => $victim->getId(),
      'target' => LOCATION_HAND,
      'deckCount' => Cards::getDeckCount(),
    ];

    // Notify receiver and victim
    self::notify($receiver, 'cardsGained', clienttranslate('You stole ${card_name} from ${player_name2}'), $data);
    self::notify($victim, 'cardsGained', clienttranslate('${player_name} stole your ${card_name}'), $data);

    // Notify everyone else
    $data['ignore'] = [$receiver, $victim];
    if ($equipped) {
      Notifications::notifyAll('cardsGained', clienttranslate('${player_name} stole ${card_name} from ${player_name2}'), $data);
    } else {
      unset($data['card_name']);
      unset($data['card']);
      Notifications::notifyAll('cardsGained', clienttranslate('${player_name} stole a card from ${player_name2}'), $data);
    }

    self::updateHand($receiver);
    self::updateHand($victim);
    self::updateDistances();
  }

  // todo change notif name
  public static function tell($msg, $args = [])
  {
    self::notifyAll('debug', $msg, $args);
  }

  /**
   * drawing a card for cards like barrel, jail, etc.
   */
  public static function flipCard($player, $card, $src, $isMessage = true)
  {
    $src_name = $src instanceof AbstractCard || $src instanceof AbstractEventCard ? $src->getName() : $src->getCharName();

    $msgOthers = $isMessage ? clienttranslate('${player_name} draws ${card_name} for ${src_name}\'s effect') : '';
    $msgYou = $isMessage ? clienttranslate('${You} draw ${card_name} for ${src_name}\'s effect') : '';
    self::notifyAll('flipCard', $msgOthers, [
      'msgYou' => $msgYou,
      'i18n' => ['src_name'],
      'player' => $player,
      'card' => $card,
      'src_name' => $src_name,
      'src_id' => $src->getId(),
      'deckCount' => Cards::getDeckCount(),
      'event' => EventCards::getActive(),
    ]);
  }

  public static function useCard($player, $card)
  {
    self::notifyAll('message', clienttranslate('${player_name} uses ${card_name}'), [
      'player' => $player,
      'card' => $card,
    ]);
  }

  /**
   * When a card moves from one player(inplay) to another player(inplay).
   * Probably needed only for dynamite
   */
  public static function moveCard($card, $player, $target)
  {
    Notifications::notifyAll('cardsGained', clienttranslate('${card_name} moves to ${player_name}'), [
      'card' => $card,
      'player' => $target,
      'player2' => $player,
      'target' => LOCATION_INPLAY,
      'src' => $player->getId(),
      'amount' => 1,
    ]);
  }

  public static function updateDistances()
  {
    self::notifyAll('updateDistances', '', [
      'distances' => Players::getDistances(),
    ]);
  }

  public static function playerEliminated($player)
  {
    /*
    THIS IS HANDLED BY BGA FRAMEWORK THAT SENDS A NOTIFICATION WHEN A PLAYER GET ELIMINATED FROM THE GAME
    self::notifyAll('playerEliminated', clienttranslate('${player_name} is eliminated'), [
      'player' => $player,
    ]);
    */
    if ($player->getRole() != SHERIFF) {
      self::notifyAll(
        'updatePlayers',
        clienttranslate('${player_name} was a ${role_name}.'),
        self::getDataToUpdatePlayer($player)
      );
      self::updateDistances();
    }
  }

  public static function revealPlayersRolesEndOfGame()
  {
    $players = Players::getLivingPlayers();
    foreach ($players as $player) {
      self::notifyAll('updatePlayersRoles', '', self::getDataToUpdatePlayer($player));
    }
  }

  private static function getDataToUpdatePlayer($player)
  {
    return [
      'i18n' => ['role_name'],
      'player' => $player,
      'role_name' => self::getRoleName($player),
      'players' => Players::getUiData(0),
    ];
  }

  private static function getRoleName($n)
  {
    return [
      clienttranslate('Sheriff'),
      clienttranslate('Deputy'),
      clienttranslate('Outlaw'),
      clienttranslate('Renegade'),
    ][$n->getRole()];
  }

  public static function reshuffle()
  {
    self::notifyAll('reshuffle', clienttranslate('Reshuffling the deck.'), [
      'deckCount' => Cards::getDeckCount(),
    ]);
  }

  /**
   * characterChosen: send all info about
   * @param Player $player
   * @param Player $character
   */
  public static function characterChosen($player)
  {
    $characterName = $player->getCharName();
    $msg = '${player_name} chose ${character_name} as a character';
    self::notifyAll('characterChosen', $msg, [
      'character_name' => $characterName,
      'character' => $player->getUiCharacterSpecificData(),
      'player' => $player,
    ]);
  }

  /**
   * @param AbstractEventCard $eventCard
   * @param AbstractEventCard $nextEventCard
   */
  public static function newEvent($eventCard, $nextEventCard)
  {
    $msg = clienttranslate('${eventActiveName} is now active!');
    self::notifyAll('newEvent', $msg, [
      'eventActive' => $eventCard,
      'eventActiveName' => $eventCard->getName(),
      'eventNext' => $nextEventCard,
      'eventsDeckCount' => EventCards::getDeckCount(),
    ]);
  }

  /**
   * playerUnconscious: send when player died but might be resurrected
   * @param Player $player
   */
  public static function playerUnconscious($player)
  {
    $msg = '${player_name} is eliminated but might be back at some point...';
    self::notifyAll('playerUnconscious', $msg, [
      'player' => $player,
    ]);
  }

  public static function playerGuessedIncorrectly($player)
  {
    $msg = '${player_name} guessed a card color incorrectly thus ending Peyote\'s effect';
    $msgYou = '${You} guessed a card color incorrectly thus ending Peyote\'s effect';
    self::notifyAll('message', $msg, [
      'msgYou' => $msgYou,
      'player' => $player,
    ]);
  }

  public static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_id'] = $data['player']->getId();
      $data['player_name'] = $data['player']->getName();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_id2'] = $data['player2']->getId();
      $data['player_name2'] = $data['player2']->getName();
      unset($data['player2']);
    }

    if (isset($data['card'])) {
      $data['card_name'] = $data['card']->getName();
      $data['i18n'][] = 'card_name';
      $data['preserve'][2] = $data['card'];
    }

    if (isset($data['ignore'])) {
      $data['preserve'][3] = 'ignore';
      $data['ignore'] = array_map(function ($player) {
        return $player->getId();
      }, $data['ignore']);
    }

    if (isset($data['eventChangedResult']) && $data['eventChangedResult']) {
      $eventName = $data['eventChangedResult']['eventName'];
      $data['flipEventMsg'] = " because of $eventName";
      $data['eventColorOverride'] = $data['eventChangedResult']['eventSuitOverride'];
      $data['preserve'][] = 'eventColorOverride';
    } else {
      $data['flipEventMsg'] = '';
    }

    if (isset($data['msgYou'])) {
      $data['preserve'][4] = 'msgYou';
    }
  }
}
