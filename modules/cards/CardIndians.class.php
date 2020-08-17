<?php

class CardIndians extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_INDIANS;
    $this->name  = clienttranslate('Indians!');
    $this->text  = clienttranslate("All other players discard a BANG! or lose 1 life point.");
    $this->color = BROWN;
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ALL_OTHER
		];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'KD', 'AD' ],
      DODGE_CITY => [ ],
    ];
  }

  public function play($player, $args) {
    BangCardManager::playCard($this->id);
    $ids = BangPlayerManager::getLivingPlayers($player->getId());
    return $player->attack($ids, false);
  }

  public function react($id, $player) {
		$player_name = BangPlayerManager::getPlayer($player->getId())->getName();

		if($id == PASS) {
			$player->looseLife(bang::$instance->getGameStateValue('currentTurn'));
		} else {
			$card = BangCardManager::getCard($id);
			BangNotificationManager::discardedCard($player, $card);
      BangCardManager::playCard($card->id);
		}
		return true;
	}

  public function getReactionCards($player) {
		return $player->getBangCards();
	}

}
