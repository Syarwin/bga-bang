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
    $players = BangPlayerManager::getLivingPlayers();
    // make the array start with this player
    foreach ($players as $idx => $pid) {
      if($player->getId()==$pid) {
        $players = array_merge(array_splice($players,$idx),$players);
        break;
      }
    }
    BangLog::addAction("selection", ['players' => $players, 'src' => $this->name]);
    BangCardManager::createSelection(count($players));
    //BangCardManager::createSelection(count($players), $player->getId());
    return "selection";
  }
}
