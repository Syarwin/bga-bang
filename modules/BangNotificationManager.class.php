<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($player, $card, $targets=[]) {
    // TODO : is card_msg useful ?
    bang::$instance->notifyAllPlayers('cardPlayed', clienttranslate('${player_name} played ${card_name} ${card_msg}'), [
      'i18n' => ['card_name', 'card_msg'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'card_msg' => $card->getArgsMessage($targets),
      'card' => $card->format(),
      'player' => $player
    ]);
  }

  public static function lostLife($player, $amount = 1) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} looses a life point') : clienttranslate('${player_name} looses ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'id' => $player->getId(),
      'hp' => $player->getHp(),
      'amount' => -$amount
    ]);

  }

  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} gains a life point') : clienttranslate('${player_name} gains ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'amount' => $amount,
      'id' => $player->getId(),
      'hp'=>$player->getHp()
    ]);
  }

  public static function gainedCard($player, $cards) {
    $amount = count($cards);
    $msg  = $amount == 1 ? clienttranslate('${player_name} draws a card') : clienttranslate('${player_name} draws ${amount} cards');
    $formattedCards = array_map(function($card){ return $card->getUIData(); }, $cards);
    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', [
      'cards' => $formattedCards,
      'src' => 'deck'
    ]);
    bang::$instance->notifyAllPlayers("updateHand", $msg, [
      'player_name' => $player->getName(),
      'id' => $player->getId(),
      'amount' => $amount,
    ]);
  }

  // TODO : make it translatable
  public static function discardedCards($player, $cards) {
    $msg  = "";
    $cardIds = [];
    foreach($cards as $idx=>$card) {
      if($idx==0) $msg = " discards ";
      elseif($idx==count($cards-1)) $msg .= " and ";
      else $msg .= ', ';
      $msg .= $card->getName();
      $cardIds[] = $card->getId();
    }
    bang::$instance->notifyPlayer($player->getId(), "cardsLost", '', ['cards'=>$cardIds]);
    bang::$instance->notifyAllPlayers("updateHand", $player->getName() . "$msg.", ['id'=>$player->getId(), 'diff'=>-count($cards)]);
  }


  public static function stoleCard($receiver, $victim, $card, $equipped) {

    if($equipped) $msg .= " stole " . $card->getName() . " from " . $victim->getName();
    else $msg .= " stole a card from " . $victim->getName();

    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', [
      'id' => $player->getId(),
      'cards' => [$card->format()],
      'src' => $victim->getId()
    ]);
    bang::$instance->notifyPlayer($player->getId(), "cardsLost", '', [
      'id' => $player->getId(),
      'cards' => [$card->format()],
      'src' => $victim->getId()
    ]);


    $msg = $equipped? clienttranslate('${player_name} stole ${card_name} from ${victim_name}')
                    : clienttranslate('${player_name} stole a card from ${victim_name}');
    $data = [
      'id'=> $receveir->getId(),
      'player_name' => $receiver->getName(),
      'victim_name' => $victim->getName(),
    ];
    if($equipped){
      $data['i18n'] = ['card_name'];
      $data['card_name'] = $card->getName();
    }

    // TODO : weird stuff with count()
//    bang::$instance->notifyAllPlayers("updateHand", $msg, ['id'=>$player->getId(), 'amount'=>count()]);
//    bang::$instance->notifyAllPlayers("updateHand", '', ['id'=>$player->getId(), 'amount'=>count()]);
  }

}
