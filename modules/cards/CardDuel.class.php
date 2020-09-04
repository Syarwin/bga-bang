<?php

class CardDuel extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_DUEL;
    $this->name  = clienttranslate('Duel');
    $this->text  = clienttranslate("A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.");
    $this->color = BROWN;
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'QD', 'JS', '8C'],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ANY
		];
  }


  /*
   *
   */
  public function getPlayOptions($player) {
 		$player_ids = BangPlayerManager::getLivingPlayers($player->getId());
 		return [
 			'type' => OPTION_PLAYER,
 			'targets' => array_values($player_ids)
 		];
  }

  public function play($player, $args) {
    $this->discard();
    BangLog::addAction("duel", ['opponent' => $args['player'] ]);
    return $player->attack([$args['player']], NO_CHECK_BARREL);
  }


  public function getReactionOptions($player) {
    return $player->getBangCards();
  }

  public function getOpponent($player){
    $p1Id = BangPlayerManager::getCurrentTurn();
    $p2Id = BangLog::getLastAction("duel")["opponent"];
    return $player->getId() == $p1Id? $p2Id : $p1Id;
  }

  public function pass($player){
    parent::pass($player);
    $current = BangPlayerManager::getCurrentTurn();
    $pId = $this->getOpponent($player);
    // if the opponent isn't the one who played the card, it doesn't count as his hit
    $enemy = $current == $pId ? BangPlayerManager::getPlayer($pId) : null;
    return $player->looseLife($enemy);
  }

  public function react($card, $player) {
    $pId = $this->getOpponent($player);
    $player->discardCard($card);
    return $player->attack([$pId], NO_CHECK_BARREL);
  }
}
