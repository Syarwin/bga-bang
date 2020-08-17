<?php

class CardDuel extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_DUEL;
    $this->name  = clienttranslate('Duel');
    $this->text  = clienttranslate("A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.");
    $this->color = BROWN;
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ANY
		];
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'QD', 'JS', '8C'],
      DODGE_CITY => [ ],
    ];
  }

  public function play($player, $args) {
    BangCardManager::playCard($this->id);
    bang::$instance->setGameStateValue('cardArg', $args['player']);
    return $player->attack([$args['player']], false);
  }

  public function react($id, $player) {
    $player_name = BangPlayerManager::getPlayer($player->getId())->getName();
    $pid = bang::$instance->getGameStateValue('currentTurn');
    if($pid == $player->getId()) $pid = bang::$instance->getGameStateValue('cardArg');
    if($id == PASS) {
      $player->looseLife(bang::$instance->getGameStateValue('currentTurn'));
      return true;
    } else {
      $card = BangCardManager::getCard($id);
      BangNotificationManager::discardedCard($player, $card);
      BangCardManager::playCard($card->id);
      $player->attack([$pid], false);
      return false;
    }
  }

  public function getReactionCards($player) {
    return $player->getBangCards();
  }

  public function getPlayOptions($player) {
		$player_ids = BangPlayerManager::getLivingPlayers($player->getID());
		return [
			'type' => OPTION_PLAYER,
			'targets' => array_values($player_ids)
		];
 	}
}
