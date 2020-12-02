<?php
namespace Bang\Cards;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Characters\Players;

class CardGeneralStore extends BrownCard {
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
    parent::play($player, $args);
    $players = Players::getLivingPlayersStartingWith($player);
    Log::addAction("selection", ['players' => $players, 'src' => $this->name, 'card' => 1]);
    Cards::createSelection(count($players));
    return "selection";
  }


  public function react($card, $player) {
    Cards::moveCard($card, 'hand', $player->getId());
    Notifications::chooseCard($player, $card);
		return null;
	}
}
