<?php
namespace Bang\Cards;
use Bang\Characters\Players;

class CardIndians extends BrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_INDIANS;
    $this->name  = clienttranslate('Indians!');
    $this->text  = clienttranslate("All other players discard a BANG! or loose 1 life point.");
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'KD', 'AD' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ALL_OTHER
		];
  }

  /* */

  public function play($player, $args) {
    parent::play($player, $args);
    $ids = $player->getOrderedOtherPlayers();
    return $player->attack($ids, NO_CHECK_BARREL);
  }


  public function getReactionOptions($player) {
		return $player->getBangCards();
	}


  public function pass($player) {
    $player->looseLife();
    return null;
  }

  public function react($card, $player) {
    $player->discardCard($card);
		return null;
	}
}
