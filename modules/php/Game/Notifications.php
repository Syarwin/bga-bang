<?php
namespace Bang\Game;
use bang;
use Bang\Characters\Players;
use Bang\Cards\Card;

/*
 * Notifications
 */
class Notifications {
  protected static function notifyAll($name, $msg, $data){
    bang::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($pId, $name, $msg, $data){
    bang::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function cardPlayed($player, $card, $args = []) {
    $msg = clienttranslate('${player_name} plays ${card_name}');
    $data = [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'card' => $card->format(),
      'playerId' => $player->getId(),
      'targetPlayer' => isset($args['player']) ? $args['player'] : null,
      'target' => $card->isEquipment() ? 'inPlay' : 'discard'
    ];


    $cardArgMsg = $card->getArgsMessage($args);
    if(!is_null($cardArgMsg) && isset($cardArgMsg['name'])){
      $msg = clienttranslate('${player_name} plays ${card_name} and chooses ${player_name2} as target');
      if(isset($args['asBang']))
        $msg = clienttranslate('${player_name} plays ${card_name} as BANG! and chooses ${player_name2} as target');

      $data['player_name2'] = $cardArgMsg['name'];
    }

    self::notifyAll('cardPlayed', $msg, $data);

    self::notifyAll("updateHand", '', [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'amount' => -1,
    ]);
  }


  public static function lostLife($player, $amount = 1) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} looses a life point') : clienttranslate('${player_name} looses ${amount} life points');
    self::notifyAll('updateHP', $msg, [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'hp' => $player->getHp(),
      'amount' => -$amount
    ]);
  }


  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} gains a life point') : clienttranslate('${player_name} gains ${amount} life points');
    self::notifyAll('updateHP', $msg, [
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

    foreach(Players::getPlayers() as $bplayer){
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

      self::notify($bplayer->getId(), "cardsGained", $msg, $data);
    }
  }

  public static function drawCardFromDiscard($player, $cards) {
    self::drawCards($player, $cards, true, 'discard');
  }

  // For general store
  public static function chooseCard($player, $card) {
    $msg = clienttranslate('${player_name} chooses ${card_name}');
    $formattedCards = [ $card->format() ];
    self::notifyAll("cardsGained", $msg, [
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
    self::notifyAll("cardLost", '', [
      'playerId' => $player->getId(),
      'card' => $card->format(),
    ]);
    if($silent)
      return;

    self::notifyAll("updateHand", clienttranslate('${player_name} discard ${card_name}'), [
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
    foreach(Players::getPlayers() as $bplayer){
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

      self::notify($bplayer->getId(), "cardsGained", $msg, $data);
    }
  }

  // todo change notif name
  public static function tell($msg, $args = []) {
    self::notifyAll('debug', $msg, $args);
  }

  // todo implement and change parameter for notification name
  /**
   * updating reaction options with arguments formatted as usual
   */
  public static function updateOptions($player, $args) {
    self::notify($player->getId(), 'updateOptions', '', $args);
  }

  /**
   * drawing a card for cards like barrel, jail, etc.
   */
  public static function drawCard($player, $card, $src) {
    $format = $card->format();
    $src_name = ($src instanceof Card) ? $src->getName() : $src->getCharName();

    self::notifyAll('drawCard', clienttranslate('${player_name} draws ${card_name} for ${src_name}\'s effect.'), [
      'i18n' => ['card_name', 'card_color', 'src_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getNameAndValue(),
      'src_name' => $src_name,
      'src_id' => $src->getId(),
      'card' => $format
    ]);
  }


  public static function useCard($player, $card){
    self::notifyAll('message', clienttranslate('${player_name} uses ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name'=>$player->getName(),
      'card_name' => $card->getName(),
    ]);
  }

  /**
   * When a card moves from one player(inplay) to another player(inplay).
   * Probably needed only for dynamite
   */
  public static function moveCard($card, $player, $target) {
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

  public static function playerEliminated($player) {
    $roles = [clienttranslate('Sheriff'), clienttranslate('Deputy'), clienttranslate('Outlaw'), clienttranslate('Renegade')];

    self::notifyAll('updatePlayers', clienttranslate('${player_name} was a ${role_name}'), [
      'i18n' => ['role_name'],
      'player_name' => $player->getName(),
      'role_name' => $roles[$player->getRole()],
      'players' => Players::getUiData(0),
    ]);
  }
}
