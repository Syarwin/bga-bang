<?php

class CardJail extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_JAIL;
    $this->name  = clienttranslate('Jail');
    $this->text  = clienttranslate("Equip any player with this. At the start of that players turn reveal top card from the deck. If it''s not heart that player is skipped. Either way, the jail is discarded.");
    $this->symbols = [
      [SYMBOL_DRAW_HEART, clienttranslate("Discard the Jail, play normally. Else discard the Jail and skip your turn.")]
    ];
    $this->copies = [
      BASE_GAME => [ 'JS', '4H', '10S' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => STARTOFTURN];
  }


  public function getPlayOptions($player) {
    // Can be played on anyone except the sheriff
		$playerIds = BangPlayerManager::getLivingPlayers(BangPlayerManager::getSherrifId());
    Utils::filter($playerIds, function($playerId) {
      $equipment = BangPlayerManager::getPlayer($playerId)->getCardsInPlay();
      foreach($equipment as $card) if($card->type == $this->type) return false;
      return true;
    });
		return [
			'type' => OPTION_PLAYER,
			'targets' => $playerIds
		];
 	}

  public function play($player, $args) {
		BangCardManager::moveCard($this->id, 'inPlay', $args['player']);
		return null;
	}


  public function activate($player, $args = []) {
    BangLog::addCardPlayed($player, $this,[]);
    $card = $player->draw($args, $this);
    if(!$card instanceof BangCard)
      return $card;

    $player->discardCard($this, true);
    $data = [
      'player_name' => $player->getName()
    ];
    if ($card->getCopyColor() == 'H') {
      BangNotificationManager::tell('${player_name} can make his turn', $data);
      return 'draw';
    } else {
      BangNotificationManager::tell('${player_name} is skipped', $data);
      return "skip";
    }
  }
}
