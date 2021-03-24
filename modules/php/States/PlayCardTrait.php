<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Helpers\Utils;

trait PlayCardTrait
{
	public function argPlayCards() {
		return [
			'_private' => [
// TODO				'active' => Players::getActive()->getHandOptions()
			]
		];
	}

	public function stPlayCard() {
    /*
		// TODO $this->setGameStateValue('JourdonnaisUsedSkill', 0);
		$players = Players::getLivingPlayers(null, true);
		$newstate = null;
		foreach($players as $player) {
			$player->checkHand();
		}
		if($newstate != null) $this->gamestate->nextState($newState);
    */
	}

	public function playCard($id, $args) {
		self::checkAction('play');
		if(in_array(Utils::getStateName(), ["react", "multiReact"])){
			$this->react($id);
			return;
		}

		// TODO : add check to see if the card was indeed playable
		// if(!in_array($id, $this->argPlayableCards())) ...
		$newState = Players::getActivePlayer()->playCard($id, $args) ?? "continuePlaying";
		if($newState == "continuePlaying") {
			Players::handleRemainingEffects();
		}
		$this->gamestate->nextState($newState);
	}
}
