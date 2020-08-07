<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($card, $player, $targets=[]) {
    $msg = $card->getArgsMessage($targets);
    $name = $card->getName();
    bang::$instance->notifyAllPlayers('cardPlayed', $player->getName() . " played $name.$msg", array('card' => $card->format(), 'player' => $player));
  }

  public static function lostLife($player) {
    bang::$instance->notifyAllPlayers('lostLife', $player->getName() . " lost a life", ['id'=>$player->getId(), 'hp'=>$player->getHp()]);
  }

}
