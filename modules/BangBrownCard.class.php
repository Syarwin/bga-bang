	<?php

/*
 * BangBrownCard: class to handle brown card
 */
class BangBrownCard extends BangCard
{
	public function getColor()	{ return BROWN; }
	public function isAction()	{ return true;  }


	/*
	 * getTargetablePlayers: return the player's id that can be targeted by this card, depending on effect and range
	 */
	public function getTargetablePlayers($player){
		$player_ids = [];
		switch($this->effect['impacts']) {
			case ALL_OTHER:
				$player_ids = BangPlayerManager::getLivingPlayers($player->id);
				break;
			case INRANGE:
				$player_ids = $player->getPlayersInRange($player->getRange());
				break;
			case SPECIFIC_RANGE:
				$player_ids = $player->getPlayersInRange($this->effect['range']);
				break;
			case ANY:
				$player_ids = BangPlayerManager::getLivingPlayers();
				break;
			case NONE:
				$player_ids = [];
				break;
		}

		return array_values($player_ids);
	}


	/*
	 * getPlayOptions
	 */
	public function getPlayOptions($player) {
		$type = -1;
		switch ($this->effect['type']) {
			case BASIC_ATTACK:
			case LIFE_POINT_MODIFIER:
				if (in_array($this->effect['impacts'], [NONE, ALL, ALL_OTHER])) {
					return ['type' => OPTION_NONE];
				}
				$type = OPTION_PLAYER;
				break;

			case DRAW:
			case DISCARD:
				$type = ($this->effect['impacts'] == NONE) ? OPTION_NONE : OPTION_CARD;
				break;

			case DEFENSIVE:
				return null;
			default:
				return ['type' => OPTION_NONE];
			break;
		}

		return [
			'type' => $type,
			'targets' => $this->getTargetablePlayers($player),
		];
 	}


	/*
	 * play
	 */
  public function play($player, $args){
		BangCardManager::discardCard($this);

		switch ($this->effect['type']) {
			case BASIC_ATTACK:
				$ids = ($this->effect['impacts'] == ALL_OTHER) ? BangPlayerManager::getLivingPlayers($player->getId()) : [$args['player']];
				return $player->attack($ids);
				break;

			case DRAW:
			case DISCARD:
				// Drawing from deck
				if($args['type'] == 'deck'){
					$player->drawCards($this->effect['amount']);
					return null;
				}

				// Drawing/discarding from someone's hand/inplay
				$victim = BangPlayerManager::getPlayer($args['player']);
				$card = $args['type'] == 'player'? $victim->getRandomCardInHand() : BangCardManager::getCard($args['arg']);

				if($this->effect['type'] == DRAW) {
					BangCardManager::moveCard($card, 'hand', $player->getId());
					BangNotificationManager::stoleCard($player, $victim, $card, $args['type'] == 'inplay');
				} else {
					$victim->discardCard($card);
				}
				$victim->onCardsLost();
				break;

			case LIFE_POINT_MODIFIER:
				$targets = [];
				if($this->effect['impacts'] == ALL) $targets = BangPlayerManager::getLivingPlayers(null, true);
				else $targets[] = is_null($args['player'])? $player : BangPlayerManager::getPlayer($args['player']);

				foreach($targets as $target) {
					$target->gainLife($this->effect['amount']);
				}
				break;
		}

		return null;
	}
}
