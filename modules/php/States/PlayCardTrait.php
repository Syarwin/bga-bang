<?php
namespace Bang\States;
use Bang\Characters\Players;
use Bang\Game\Utils;

trait PlayCardTrait
{
	public function argPlayCards() {
		return [
			'_private' => [
				'active' => Players::getActivePlayer()->getHandOptions()
			]
		];
	}

	public function stPlayCard() {
		$this->setGameStateValue('JourdonnaisUsedSkill', 0);
		$players = Players::getLivingPlayers(null, true);
		$newstate = null;
		foreach($players as $player) {
			$player->checkHand();
		}
		if($newstate != null) $this->gamestate->nextState($newState);
	}

	public function playCard($id, $args) {
		self::checkAction('play');
		if(in_array(Utils::getStateName(), ["react", "multiReact"])){
			$this->react($id);
			return;
		}

		// TODO : add check to see if the card was indeed playable
		// if(!in_array($id, $this->argPlayableCards())) ...
		$newState = Players::getActivePlayer()->playCard($id, $args);
		$this->gamestate->nextState($newState ?? "continuePlaying");
	}
}
