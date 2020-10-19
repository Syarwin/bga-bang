<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($player, $card, $args = []) {
    bang::$instance->notifyAllPlayers('cardPlayed', clienttranslate('${player_name} plays ${card_name}${card_msg}'), [
      'i18n' => ['card_name', 'card_msg'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'card_msg' => $card->getArgsMessage($args),
      'card' => $card->format(),
      'playerId' => $player->getId(),
      'targetPlayer' => isset($args['player']) ? $args['player'] : null,
      'target' => $card->isEquipment() ? 'inPlay' : 'discard'
    ]);

    bang::$instance->notifyAllPlayers("updateHand", '', [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'amount' => -1,
    ]);
  }


  public static function lostLife($player, $amount = 1) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} looses a life point') : clienttranslate('${player_name} looses ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'hp' => $player->getHp(),
      'amount' => -$amount
    ]);
  }


  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} gains a life point') : clienttranslate('${player_name} gains ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'amount' => $amount,
      'playerId' => $player->getId(),
      'hp'=>$player->getHp()
    ]);
  }

  public static function drawCards($player, $cards, $public = false, $src = 'deck') {
    $amount = count($cards);
    $srcName = $src == 'deck'? clienttranslate("the deck") : clienttranslate("the discard pile");
    $formattedCards = array_map(function($card){ return $card->format(); }, $cards);

    foreach(BangPlayerManager::getPlayers() as $bplayer){
      $isVisible = ($public || $player->getId() == $bplayer->getId());
      $data = [
        'i18n' => ['src_name'],
        'src_name' => $srcName,
        'player_name' => $player->getName(),
        'playerId' => $player->getId(),
        'amount' => $amount,
        'cards' => $isVisible? $formattedCards : [],
        'src' => $src,
        'target' => 'hand',
      ];

      if($amount == 1){
        if($isVisible){
          $msg = $src == 'deck'? clienttranslate('${player_name} draws ${card_name} from ${src_name}')
                : clienttranslate('${player_name} chooses ${card_name} from ${src_name}');
          $data['card_name'] = $cards[0]->getNameAndValue();
          $data['i18n'][] = 'card_name';
        }
        else
          $msg = clienttranslate('${player_name} draws a card from ${src_name}');
      }
      else
        $msg = clienttranslate('${player_name} draws ${amount} cards from ${src_name}');

      bang::$instance->notifyPlayer($bplayer->getId(), "cardsGained", $msg, $data);
    }
  }

  public static function drawCardFromDiscard($player, $cards) {
    self::drawCards($player, $cards, true, 'discard');
  }

  // For general store
  public static function chooseCard($player, $card) {
    $msg = clienttranslate('${player_name} chooses ${card_name}');
    $formattedCards = [ $card->format() ];
    bang::$instance->notifyAllPlayers("cardsGained", $msg, [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'playerId' => $player->getId(),
      'amount' => 1,
      'cards' => $formattedCards,
      'src' => 'deck',
      'target' => 'hand',
    ]);
  }



  public static function discardedCard($player, $card, $silent = false) {
    bang::$instance->notifyAllPlayers("cardLost", '', [
      'playerId' => $player->getId(),
      'card' => $card->format(),
    ]);
    if($silent)
      return;

    bang::$instance->notifyAllPlayers("updateHand", clienttranslate('${player_name} discard ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'playerId' => $player->getId(),
      'amount' => -1
    ]);
  }

  public static function discardedCards($player, $cards, $silent = false) {
    foreach($cards as $card){
      self::discardedCard($player, $card, $silent);
    }
  }


  public static function stoleCard($receiver, $victim, $card, $equipped) {
    foreach(BangPlayerManager::getPlayers() as $bplayer){
      $show = ($equipped || in_array($bplayer->getId(), [$receiver->getId(), $victim->getId()]));
      $msg = $show? clienttranslate('${player_name} stole ${card_name} from ${victim_name}')
                  : clienttranslate('${player_name} stole a card from ${victim_name}');
      $data = [
        'player_name' => $receiver->getName(),
        'victim_name' => $victim->getName(),
        'playerId' => $receiver->getId(),
        'victimId' => $victim->getId(),
        'amount' => 1,
        'cards' => $show? [$card->format()] : [],
        'src' => $victim->getId(),
        'target' => 'hand',
      ];
      if($show){
        $data['i18n'] = ['card_name'];
        $data['card_name'] = $card->getName();
      }

      bang::$instance->notifyPlayer($bplayer->getId(), "cardsGained", $msg, $data);
    }
  }

  // todo change notif name
  public static function tell($msg, $args = []) {
    bang::$instance->notifyAllPlayers('debug', $msg, $args);
  }

  // todo implement and change parameter for notification name
  /**
   * updating reaction options with arguments formatted as usual
   */
  public static function updateOptions($player, $args) {
    bang::$instance->notifyPlayer($player->getId(), 'updateOptions', '', $args);
  }

  /**
   * drawing a card for cards like barrel, jail, etc.
   */
  public static function drawCard($player, $card, $src) {

    $format = $card->format();
    $src_name = ($src instanceof BangCard) ? $src->getName() : $src->getCharName();

    bang::$instance->notifyAllPlayers('drawCard', '${player_name} draws ${card_name} for ${src_name}\'s effect.', [
      'i18n' => ['card_name', 'card_color', 'src_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getNameAndValue(),
      'src_name' => $src_name,
      'src_id' => $src->getId(),
      'card' => $format
    ]);
  }

  /**
   * When a card moves from one player(inplay) to another player(inplay).
   * Probably needed only for dynamite
   */
  public static function moveCard($card, $player, $target) {
    bang::$instance->notifyAllPlayers('cardsGained', '${card_name} moves to ${player_name}', [
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

  public static function playerEliminated($player) {
    $roles = [clienttranslate('Sheriff'), clienttranslate('Deputy'), clienttranslate('Outlaw'), clienttranslate('Renegade')];
    bang::$instance->notifyAllPlayers('playerEliminated', '${player_name} has been eliminated, he was a ${role_name}', [
      'i18n' => ['role_name'],
      'player_name' => $player->getName(),
      'role_name' => $roles[$player->getRole()],
      'playerId' => $player->getId(),
    ]);

    bang::$instance->notifyAllPlayers('updatePlayers', '', [
      'players' => BangPlayerManager::getUiData(0),
    ]);
  }

}
