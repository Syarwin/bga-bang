<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($player, $card, $targets=[]) {
    $msg = $card->getArgsMessage($targets);
    $name = $card->getName();
    bang::$instance->notifyAllPlayers('cardPlayed', $player->getName() . " played $name.$msg", array('card' => $card->format(), 'player' => $player));
  }

  public static function lostLife($player) {
    bang::$instance->notifyAllPlayers('lostLife', $player->getName() . " lost a life point.", ['id'=>$player->getId(), 'hp'=>$player->getHp()]);
  }

  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? " gained a life point." : " gained $amount life points.";
    bang::$instance->notifyAllPlayers('gainedLife', $player->getName() . $msg, ['id'=>$player->getId(), 'hp'=>$player->getHp()]);
  }

  public static function gainedCard($player, $cards) {

  }

  public static function discardedCards($player, $cards) {

  }

  public static function stoleCards($receiver, $victim, $card) {

  }

}
