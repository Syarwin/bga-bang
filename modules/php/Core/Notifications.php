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
    self::updateArgs($data);
    bang::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($pId, $name, $msg, $data)
  {
    self::updateArgs($data);
    $pId = is_int($pId) ? $pId : $pId->getId();
    bang::get()->notifyPlayer($pId, $name, $msg, $data);
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
      'target' => $card->isEquipment() ? 'inPlay' : 'discard',
    ];

    if (isset($args['player'])) {
      $msg = clienttranslate('${player_name} plays ${card_name} and chooses ${player_name2} as target');
      $data['msgYou'] = clienttranslate('${You} play ${card_name} and choose ${player_name2} as target');
      if (isset($args['asBang'])) {
        $msg = clienttranslate('${player_name} plays ${card_name} as BANG! and chooses ${player_name2} as target');
        $data['msgYou'] = clienttranslate('${You} play ${card_name} as BANG! and choose ${player_name2} as target');
      }

      $data['player2'] = Players::get($args['player']);
    }

    self::notifyAll('cardPlayed', $msg, $data);

    self::notifyAll('updateHand', '', [
      'player' => $player,
      'amount' => -1,
    ]);
  }

  public static function lostLife($player, $amount = 1)
  {
    $msg = clienttranslate('${player_name} loses ${amount} life points');
    $sgYou = clienttranslate('${You} lose ${amount} life points');
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
    $sgYou = clienttranslate('${You} gain ${amount} life points');
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

  public static function drawCards($player, $cards, $public = false, $src = 'deck')
  {
    $amount = $cards->count();
    $data = [
      'i18n' => ['src_name'],
      'src_name' => $src == 'deck' ? clienttranslate('the deck') : clienttranslate('the discard pile'),
      'player' => $player,
      'amount' => $amount,
      'cards' => $cards->ui()->toArray(),
      'src' => $src,
      'target' => 'hand',
      'deckCount' => Cards::getDeckCount(),
    ];

    // Notify player
    if ($amount == 1) {
      $msg =
        $src == 'deck'
          ? clienttranslate('${You} draw ${card_name} from ${src_name}')
          : clienttranslate('${You} choose ${card_name} from ${src_name}');
      $data['card'] = $cards->first();
    } else {
      $msg = clienttranslate('${You} draw ${amount} cards from ${src_name}');
    }
    self::notify($player, 'cardsGained', $msg, $data);

    // Notify everyone else
    if (!$public) {
      unset($data['card_name']);
      unset($data['card']);
      unset($data['cards']);
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
    $data['ignore'] = [$player];
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
    self::notifyAll('cardsGained', $msg, [
      'msgYou' => clienttranslate('${You} choose ${card_name}'),
      'player' => $player,
      'card' => $card,
      'amount' => 1,
      'src' => 'deck',
      'target' => 'hand',
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
      'player' => $receiver,
      'player2' => $victim,
      'card' => $card,
      'amount' => 1,
      'src' => $victim->getId(),
      'target' => 'hand',
      'deckCount' => Cards::getDeckCount(),
    ];

    // Notify receiver and victim
    self::notify($receiver, 'cardsGained', clienttranslate('${You} stole ${card_name} from ${player_name2}'), $data);
    self::notify($victim, 'cardsGained', clienttranslate('${player_name} stole you ${card_name}'), $data);

    // Notify everyone else
    $data['ignore'] = [$receiver, $victim];
    if ($equipped) {
      self::notifyAll('cardsGained', clienttranslate('${player_name} stole ${card_name} from ${player_name2}'), $data);
    } else {
      unset($data['card_name']);
      unset($data['card']);
      self::notifyAll('cardsGained', clienttranslate('${player_name} stole a card from ${player_name2}'), $data);
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
    $src_name = $src instanceof Card ? $src->getName() : $src->getCharName();

    self::notifyAll('flipCard', clienttranslate('${player_name} draws ${card_name} for ${src_name}\'s effect.'), [
      'i18n' => ['src_name'],
      'player' => $player,
      'card' => $card,
      'src_name' => $src_name,
      'src_id' => $src->getId(),
      'deckCount' => Cards::getDeckCount(),
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
    self::notifyAll('cardsGained', clienttranslate('${card_name} moves to ${player_name}'), [
      'card' => $card,
      'player' => $target,
      'player2' => $player,
      'target' => 'inPlay',
      'src' => $player->getId(),
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
      'player' => $player,
    ]);

    self::notifyAll('updatePlayers', clienttranslate('${player_name} was a ${role_name}.'), [
      'i18n' => ['role_name'],
      'player' => $player,
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

    if (isset($data['msgYou'])) {
      $data['preserve'][4] = 'msgYou';
    }
  }
}
