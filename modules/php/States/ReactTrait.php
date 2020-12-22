<?php
namespace Bang\States;
use Bang\Characters\Players;
use Bang\Cards\Cards;
use Bang\Game\Log;
use Bang\Game\Utils;
use Bang\Game\Notifications;
use Bang\Game\Stats;

trait ReactTrait
{
	public function stAwaitReaction() {
		$args = Log::getLastAction("react");

		//Utils::die();
		if(count($args['order'])>1) {
			foreach ($args['order'] as $pId) {

				if(count($args['_private'][$pId]['selection']) > 0) continue;
				$this->gamestate->changeActivePlayer($pId);
				break;
			}
		} else {
			$this->gamestate->changeActivePlayer($pId);
		}
		$this->gamestate->nextState('single');
	}

	public function argReact() {
	 return Log::getLastAction("react");
	}

	function react($ids) {
		$pId = self::getCurrentPlayerId();
 		$player = Players::getPlayer($pId);
		$argReact = $this->argReact();
		$argReact["_private"][$player->getId()]['selection'] = $ids;
		if($pId == self::getActivePlayerId()) {
	 		$newState = $player->react($ids);
			if($newState == "updateOptions") {
	      // TODO : the computation is not correct, it should handle more complex case
				// example?
				$this->updateOptions($player, $argReact);
			} else {
				while(true) {
					$next = array_reduce($argReact['order'],
						function($carry, $id) use ($pId){
							if($carry==null) return $id == $pId ? $id : null;
							if($carry == $pId) return $id;
							return $carry;
						});
					if($next == $pId) break;
					$selection = $argReact['_private'][$next]['selection'];
					if(count($selection)>0) {
						$pId = $next;
						$player = Players::getPlayer($pId);
						$newState = $player->react($selection);
						if($newState == "updateOptions") {
							$this->updateOptions($player, $argReact);
							return;
						}
					} else {
						$newState = 'react';
						break;
					}
				}
				Log::addAction("react", $argReact);
        $this->gamestate->nextState($newState ?? "finishedReaction");
			}
		} else {
			Log::addAction("react", $argReact);
		}

 	}

	public function updateOptions($player, $argReact) {
		$args = Cards::getCurrentCard()->getReactionOptions($player);
		Notifications::updateOptions($player, $args);

		$argReact["_private"][$player->getId()] = $args;
		$argReact["_private"][$player->getId()]['src'] =  Log::getCurrentCard();
		Log::addAction("react", $argReact);
	}

	public function useAbility($args) {
		$id = self::getCurrentPlayerId();
		Players::getPlayer($id)->useAbility($args);
	}



	public function stEndReaction() {
		$args = Log::getLastAction('react');
    // Handle direct elimination with no cards in hand
    $src = is_null($args)? 'hp' : array_values($args['_private'])[0]['src'];

		$toEliminate = Players::getPlayersForElimination();

    // What else could that be ??
		if($src == 'hp') {
      // Find the next living player after current player that is not going to be eliminated
			$living = Players::getLivingPlayersStartingWith(Players::getCurrentTurn(true));
			foreach($living as $id) {
				if(!in_array($id, $toEliminate)) {
					if($id != self::getActivePlayerId()) $this->gamestate->changeActivePlayer($id);
					break;
				}
			}

			$nextState = array_reduce($toEliminate, function($state, $id){ return Players::getPlayer($id)->eliminate() ?? $state;}, null);
			if(is_null($nextState)) {
				if(Players::getCurrentTurn(true)->isEliminated())
					$nextState = 'next';
				else
					$nextState = Log::getLastAction('lastState')[0] == 'startOfTurn' ? 'draw' : 'finishedReaction';
			}
			if($nextState == "finishedReaction") {
				Players::handleRemainingEffects();
				Cards::resetPlayedColumn();
			}
			$this->gamestate->nextState($nextState);
			return;
		}

		$this->gamestate->changeActivePlayer(Players::getCurrentTurn());
		if(count($toEliminate)>0) $this->gamestate->nextState('eliminate');
		else {
			Players::handleRemainingEffects();
			Cards::resetPlayedColumn();
			$this->gamestate->nextState("finishedReaction");
		}
	}
}
