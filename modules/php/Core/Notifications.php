<?php
namespace BANG\Core;
use bang;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Cards\Card;

/*
 * Notifications
 */
class Notifications
{
  protected static function notifyAll($name, $msg, $data)
  {
    bang::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($pId, $name, $msg, $data)
  {
    $pId = is_int($pId) ? $pId : $pId->getId();
    bang::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function cardPlayed($player, $card, $args = [])
  {
    $msg = clienttranslate('${player_name} plays ${card_name}');
    $data = [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'card' => $card,
      'playerId' => $player->getId(),
      'targetPlayer' => isset($args['player']) ? $args['player'] : null,
      'target' => $card->isEquipment() ? 'inPlay' : 'discard',
    ];

    $cardArgMsg = $card->getArgsMessage($args);
    if (!is_null($cardArgMsg) && isset($cardArgMsg['name'])) {
      $msg = clienttranslate('${player_name} plays ${card_name} and chooses ${player_name2} as target');
      if (isset($args['asBang'])) {
        $msg = clienttranslate('${player_name} plays ${card_name} as BANG! and chooses ${player_name2} as target');
      }

      $data['player_name2'] = $cardArgMsg['name'];
    }

    self::notifyAll('cardPlayed', $msg, $data);

    self::notifyAll('updateHand', '', [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'amount' => -1,
    ]);
  }

  public static function lostLife($player, $amount = 1)
  {
    $msg =
      $amount == 1
        ? clienttranslate('${player_name} looses a life point')
        : clienttranslate('${player_name} looses ${amount} life points');
    self::notifyAll('updateHP', $msg, [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'hp' => $player->getHp(),
      'amount' => -$amount,
    ]);
  }

  public static function gainedLife($player, $amount)
  {
    $msg =
      $amount == 1
        ? clienttranslate('${player_name} gains a life point')
        : clienttranslate('${player_name} gains ${amount} life points');
    self::notifyAll('updateHP', $msg, [
      'player_name' => $player->getName(),
      'amount' => $amount,
      'playerId' => $player->getId(),
      'hp' => $player->getHp(),
    ]);
  }

  public static function drawCards($player, $cards, $public = false, $src = 'deck')
  {
    $amount = $cards->count();
    $data = [
      'i18n' => ['src_name'],
      'src_name' => $src == 'deck' ? clienttranslate('the deck') : clienttranslate('the discard pile'),
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'amount' => $amount,
      'cards' => $cards->ui(),
      'src' => $src,
      'target' => 'hand',
      'deckCount' => Cards::getDeckCount(),
    ];

    // Notify player
    if ($amount == 1) {
      $msg =
        $src == 'deck'
          ? clienttranslate('You draw ${card_name} from ${src_name}')
          : clienttranslate('You chooses ${card_name} from ${src_name}');
      $data['card_name'] = $cards->first()->getNameAndValue();
      $data['i18n'][] = 'card_name';
    } else {
      $msg = clienttranslate('You draws ${amount} cards from ${src_name}');
    }
    self::notify($player, 'cardsGained', $msg, $data);

    // Notify everyone else
    if (!$public) {
      unset($data['card_name']);
      $data['cards'] = [];
      $msg =
        $amount == 1
          ? clienttranslate('${player_name} draws a card from ${src_name}')
          : clienttranslate('${player_name} draws ${amount} cards from ${src_name}');
    } else {
      $msg =
        $src == 'deck'
          ? clienttranslate('${player_name} draws ${card_name} from ${src_name}')
          : clienttranslate('${player_name} chooses ${card_name} from ${src_name}');
    }
    $data['ignore'] = [$player->getId()];
    $data['preserve'] = ['ignore'];
    $data['preserveArgsInHistory'] = ['ignore'];
    $data['_preserve'] = ['ignore'];
    self::notifyAll('cardsGained', $msg, $data);
  }

  public static function drawCardFromDiscard($player, $cards)
  {
    self::drawCards($player, $cards, true, 'discard');
  }

  // For general store
  public static function chooseCard($player, $card)
  {
    $msg = clienttranslate('${player_name} chooses ${card_name}');
    $formattedCards = [$card->format()];
    self::notifyAll('cardsGained', $msg, [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'playerId' => $player->getId(),
      'amount' => 1,
      'cards' => $formattedCards,
      'src' => 'deck',
      'target' => 'hand',
      'deckCount' => Cards::getDeckCount(),
    ]);
  }

  public static function discardedCard($player, $card, $silent = false)
  {
    self::notifyAll('cardLost', '', [
      'playerId' => $player->getId(),
      'card' => $card->jsonSerialize(),
    ]);
    if ($silent) {
      return;
    }

    self::notifyAll('updateHand', clienttranslate('${player_name} discard ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'playerId' => $player->getId(),
      'amount' => -1,
    ]);
  }

  public static function discardedCards($player, $cards, $silent = false)
  {
    foreach ($cards as $card) {
      self::discardedCard($player, $card, $silent);
    }
  }

  public static function stoleCard($receiver, $victim, $card, $equipped)
  {
    $data = [
      'i18n' => ['card_name'],
      'player_name' => $receiver->getName(),
      'victim_name' => $victim->getName(),
      'card_name' => $card->getName(),
      'playerId' => $receiver->getId(),
      'victimId' => $victim->getId(),
      'amount' => 1,
      'cards' => [$card],
      'src' => $victim->getId(),
      'target' => 'hand',
      'deckCount' => Cards::getDeckCount(),
    ];

    // Notify receiver and victim
    self::notify($receiver, 'cardsGained', clienttranslate('You stole ${card_name} from ${victim_name}'), $data);
    self::notify($victim, 'cardsGained', clienttranslate('{player_name} stole you ${card_name}'), $data);

    // Notify everyone else
    $data['ignore'] = [$receiver->getId(), $victim->getId()];
    $data['preserve'] = ['ignore'];
    if ($equipped) {
      self::notifyAll('cardGained', clienttranslate('${player_name} stole ${card_name} from ${victim_name}'), $data);
    } else {
      $data['card_name'] = '';
      $data['cards'] = [];
      self::notifyAll('cardGained', clienttranslate('${player_name} stole a card from ${victim_name}'), $data);
    }
  }

  // todo change notif name
  public static function tell($msg, $args = [])
  {
    self::notifyAll('debug', $msg, $args);
  }

  // todo implement and change parameter for notification name
  /**
   * updating reaction options with arguments formatted as usual
   */
  public static function updateOptions($player, $args)
  {
    self::notify($player->getId(), 'updateOptions', '', $args);
  }

  /**
   * drawing a card for cards like barrel, jail, etc.
   */
  public static function flipCard($player, $card, $src)
  {
    $format = $card->format();
    $src_name = $src instanceof Card ? $src->getName() : $src->getCharName();

    self::notifyAll('flipCard', clienttranslate('${player_name} draws ${card_name} for ${src_name}\'s effect.'), [
      'i18n' => ['card_name', 'card_color', 'src_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getNameAndValue(),
      'src_name' => $src_name,
      'src_id' => $src->getId(),
      'card' => $format,
      'deckCount' => Cards::getDeckCount(),
    ]);
  }

  public static function useCard($player, $card)
  {
    self::notifyAll('message', clienttranslate('${player_name} uses ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
    ]);
  }

  /**
   * When a card moves from one player(inplay) to another player(inplay).
   * Probably needed only for dynamite
   */
  public static function moveCard($card, $player, $target)
  {
    self::notifyAll('cardsGained', clienttranslate('${card_name} moves to ${player_name}'), [
      'i18n' => ['card_name'],
      'card_name' => $card->getName(),
      'player_name' => $target->getName(),
      'playerId' => $target->getId(),
      'victimId' => $player->getId(),
      'target' => 'inPlay',
      'src' => $player->getId(),
      'cards' => [$card->format()],
      'amount' => 1,
    ]);
  }

  public static function playerEliminated($player)
  {
    $roles = [
      clienttranslate('Sheriff'),
      clienttranslate('Deputy'),
      clienttranslate('Outlaw'),
      clienttranslate('Renegade'),
    ];

    self::notifyAll('playerEliminated', clienttranslate('${player_name} is eliminated.'), [
      'player_name' => $player->getName(),
      'who_quits' => $player->getId(),
    ]);

    self::notifyAll('updatePlayers', clienttranslate('${player_name} was a ${role_name}.'), [
      'i18n' => ['role_name'],
      'player_name' => $player->getName(),
      'role_name' => $roles[$player->getRole()],
      'players' => Players::getUiData(0),
    ]);
  }

  public static function reshuffle()
  {
    self::notifyAll('reshuffle', clienttranslate('Reshuffling the deck.'), [
      'deckCount' => Cards::getDeckCount(),
    ]);
  }

  public static function preSelectCards($player, $ids)
  {
    self::notify($player->getId(), 'preselection', '', [
      'cards' => $ids,
    ]);
  }
}
