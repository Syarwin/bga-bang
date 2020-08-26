<?php

class VultureSam extends BangPlayer {
  public function __construct($row = null)  {
    $this->character    = VULTURE_SAM;
    $this->character_name = clienttranslate('Vulture Sam');
    $this->text  = [
      clienttranslate("Whenever a character is eliminated from the play, he takes all the cards of that player."),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function onPlayerEliminated($player) {
    //$cards = array_merge($player->getCardsInHand(), $player->getCardsInPlay());
    // TODO send a single notification?
    foreach($player->getCardsInHand() as $card) {
      BangCardManager::moveCard($card, 'hand', $this->id);
      BangNotificationManager::stoleCard($this, $player, false);
    }
    foreach($player->getCardsInPlay() as $card) {
      BangCardManager::moveCard($card, 'hand', $this->id);
      BangNotificationManager::stoleCard($this, $player, true);
    }
  }
}
