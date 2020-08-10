<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($player, $card, $targets=[]) {
    $msg = $card->getArgsMessage($targets);
    $name = $card->getName();
    bang::$instance->notifyAllPlayers('cardPlayed', $player->getName() . " plays $name.$msg", array('card' => $card->format(), 'player' => $player));
  }

  public static function lostLife($player, $amount = 1) {
    $msg  = $amount == 1 ? " looses a life point." : " looses $amount life points.";
    bang::$instance->notifyAllPlayers('updateHP', $player->getName() . $msg, ['id'=>$player->getId(), 'hp'=>$player->getHp(), 'amount'=>-$amount]);
  }

  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? " gains a life point." : " gains $amount life points.";
    bang::$instance->notifyAllPlayers('updateHP', $player->getName() . $msg, ['id'=>$player->getId(), 'hp'=>$player->getHp(), 'amount'=>$amount]);
  }

  public static function gainedCard($player, $cards) {
    $amount = count($cards);
    $msg  = count($cards) == 1 ? " draws a card." : " draws $amount cards.";
    $formattedCards = [];
    foreach($cards as $card) $formattedCards[] = $card->getUIData();
    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', ['cards'=>$formattedCards, 'src'=>'deck']);
    bang::$instance->notifyAllPlayers("updateHand", $player->getName() . $msg, ['id'=>$player->getId(), 'diff'=>$amount]);
  }

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
    $msg = $receiver->getName();
    if($equipped) $msg .= " stole " . $card->getName() . " from " . $victim->getName();
    else $msg .= " stole a card from " . $victim->getName();
    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', ['id'=>$player->getId(), 'cards'=>[$card->format()], 'src'=>$victim->getId()]);
    bang::$instance->notifyPlayer($player->getId(), "cardsLost", '', ['id'=>$player->getId(), 'cards'=>[$card->format()], 'src'=>$victim->getId()]);
    bang::$instance->notifyAllPlayers("updateHand", $msg, ['id'=>$player->getId(), 'amount'=>count()]);
    bang::$instance->notifyAllPlayers("updateHand", '', ['id'=>$player->getId(), 'amount'=>count()]);
  }

}
