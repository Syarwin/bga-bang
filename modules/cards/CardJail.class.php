<?php

class CardJail extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type    = CARD_JAIL;
    $this->name  = clienttranslate('Jail');
    $this->text  = clienttranslate("Equip any player with this. At the start of that players turn reveal top card from the deck. If it''s not heart that player is skipped. Either way, the jail is discarded.");
    $this->color = BLUE;
    $this->effect = ['type' => STARTOFTURN];
    $this->symbols = [
      [SYMBOL_DRAW_HEART, clienttranslate("Discard tha Jail, play normally. Else discard the Jail and skip your turn.")]
    ];
    $this->copies = [
      BASE_GAME => [ 'JS', '4H', '10S' ],
      DODGE_CITY => [ ],
    ];
  }

  public function play($player, $args) {
		BangCardManager::moveCard($this->id, 'inPlay',$args['player']);
		return true;
  }

  public function getPlayOptions($player) {
		$player_ids = BangPlayerManager::getLivingPlayers($player->getID());
    $sherrif = BangPlayerManager::getSherrifId();
    Utils::filter($player_ids, function($id) use ($sherrif){return $id!=$sherrif;});
		return [
			'type' => OPTION_PLAYER,
			'targets' => array_values($player_ids)
		];
 	}

  public function activate($player, $args=[]) {
    $card = $player->draw($args, $this);
    if(is_null($card)) return;
    BangCardManager::playCard($this->id);
    $name = $player->getName();
    BangNotificationManager::discardedCard($player, $this, true);
    if ($card->format()['color'] == 'H') {
      BangNotificationManager::tell("$name can make his turn");
      return null;
    } else {
      BangNotificationManager::tell("$name is skipped");
      return "skip";
    }
  }
}
