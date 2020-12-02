<?php
namespace Bang\Cards;
use Bang\Characters\Players;
use Bang\Game\Notifications;

/*
 * BrownCard: class to handle brown card
 */
class BrownCard extends Card
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
				$player_ids = Players::getLivingPlayers($player->id);
				break;
			case INRANGE:
				$player_ids = $player->getPlayersInRange($player->getRange());
				break;
			case SPECIFIC_RANGE:
				$player_ids = $player->getPlayersInRange($this->effect['range']);
				break;
			case ANY:
				$player_ids = Players::getLivingPlayers();
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
		Cards::discardCard($this);

		switch ($this->effect['type']) {
			case BASIC_ATTACK:
				$ids = ($this->effect['impacts'] == ALL_OTHER) ? Players::getLivingPlayers($player->getId()) : [$args['player']];
				return $player->attack($ids);
				break;

			case DRAW:
			case DISCARD:
				// Drawing from deck
				if(is_null($args['type'])) {
					$player->drawCards($this->effect['amount']);
					return null;
				}

				// Drawing/discarding from someone's hand/inplay
				$victim = Players::getPlayer($args['player']);
				$card = $args['type'] == 'player'? $victim->getRandomCardInHand() : Cards::getCard($args['arg']);

				if($this->effect['type'] == DRAW) {
					Cards::moveCard($card, 'hand', $player->getId());
					Notifications::stoleCard($player, $victim, $card, $args['type'] == 'inplay');
				} else {
					$victim->discardCard($card);
				}
				$victim->onCardsLost();
				break;

			case LIFE_POINT_MODIFIER:
				$targets = [];
				if($this->effect['impacts'] == ALL) $targets = Players::getLivingPlayers(null, true);
				else $targets[] = is_null($args['player'])? $player : Players::getPlayer($args['player']);

				foreach($targets as $target) {
					$target->gainLife($this->effect['amount']);
				}
				break;
		}

		return null;
	}
}
