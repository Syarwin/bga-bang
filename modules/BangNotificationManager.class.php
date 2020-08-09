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

  public static function lostLife($player) {
    bang::$instance->notifyAllPlayers('lostLife', clienttranslate('${player_name} lost a life point'), [
      'player_name' => $player->getName(),
      'id' => $player->getId(),
      'hp' => $player->getHp()
    ]);
  }

  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} gained a life point') : clienttranslate('${player_name} gained ${amount} life points');
    bang::$instance->notifyAllPlayers('gainedLife', $msg, [
      'player_name' => $player->getName(),
      'amount' => $amount,
      'id' => $player->getId(), 
      'hp'=>$player->getHp()
    ]);
  }

  public static function gainedCard($player, $cards) {

  }

  public static function discardedCards($player, $cards) {

  }

  public static function stoleCards($receiver, $victim, $card) {

  }

}
