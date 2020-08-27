<?php

class CardGeneralStore extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_GENERAL_STORE;
    $this->name  = clienttranslate('General Store');
    $this->text  = clienttranslate("Reveal as many cards as players left. Each player chooses one, starting with you");
    $this->symbols = [
      [clienttranslate("Reveal as many card as players. Each player draws one.")]
    ];
    $this->copies = [
      BASE_GAME => [ '9C', 'QS' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => OTHER];
  }

  public function play($player, $args) {
    $players = BangPlayerManager::getLivingPlayersStartingWith($player);
    BangLog::addAction("selection", ['players' => $players, 'src' => $this->name, 'card' => 1]);
    BangCardManager::createSelection(count($players));
    return "selection";
  }


  public function react($card, $player) {
    // TODO : missing notification
    BangCardManager::moveCard($card, 'hand', $player->getId());
    BangNotificationManager::gainedCards($player, [$card], true);
		return null;
	}
}
