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
		Cards::resetPlayedColumn();
		$pId = Log::getReactPlayers();
		if(is_array($pId)) {
			$this->gamestate->setPlayersMultiactive($pId, 'finishedReaction', true); // This transition should never happens as the targets are non-empty
			$this->gamestate->nextState('multi');
		} else {
			$this->gamestate->changeActivePlayer($pId);
			$this->gamestate->nextState('single');
		}
	}

	public function argReact() {
	 return Log::getLastAction("react");
	}


	public function stAwaitMultiReaction() {
		Cards::resetPlayedColumn();
		$players = Log::getReactPlayers();
		$this->gamestate->setPlayersMultiactive($players, 'finishedReaction', true); // This transition should never happens as the targets are non-empty
		$this->gamestate->nextState();
	}


	function react($ids) {
 		$player = Players::getPlayer(self::getCurrentPlayerId());
 		$newState = $player->react($ids) ?? "finishedReaction";

		if($newState == "updateOptions"){
      // TODO : the computation is not correct, it should handle more complex case
			$args = Cards::getCurrentCard()->getReactionOptions($player);
      Notifications::updateOptions($player, $args);

      $argReact = $this->argReact();
      $argReact["_private"][$player->getId()] = $args;
      $argReact["_private"][$player->getId()]['src'] =  Log::getCurrentCard();
      Log::addAction("react", $argReact);
		} else {
	    if(Utils::getStateName() == 'multiReact') {
	      $this->gamestate->setPlayerNonMultiactive(self::getCurrentPlayerId(), $newState);
	    } else {
        $this->gamestate->nextState($newState);
      }
		}
 	}

	public function useAbility($args) {
		$id = self::getCurrentPlayerId();
		Players::getPlayer($id)->useAbility($args);
	}



	public function stEndReaction() {
		$args = Log::getLastAction('react');
    $src = array_values($args['_private'])[0]['src'];

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
			$this->gamestate->nextState($nextState);
			return;
		}

		$this->gamestate->changeActivePlayer(Players::getCurrentTurn());
		if(count($toEliminate)>0) $this->gamestate->nextState('eliminate');
		else $this->gamestate->nextState("finishedReaction");
	}
}
