<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;
use BANG\Core\Stats;

trait ReactTrait
{
	public function stAwaitReaction() {
		$args = Log::getLastAction("react");
    $this->gamestate->changeActivePlayer($args['order'][0]);
		$this->gamestate->nextState('single');
	}

	public function argReact() {
	 return []; //Log::getLastAction("react");
	}


  function reactAux($pId, $ids){
    $player = Players::getPlayer($pId);
		$argReact = $this->argReact();
    $newState = $player->react($ids);

    // New options : can happens when failing on a barrel for instance
    if($newState == "updateOptions") {
      // TODO : the computation is not correct, it should handle more complex case
      // example?
      $this->updateOptions($player, $argReact);
    }
    // Otherwise, reaction is over, proceed to next player if any
    else {
      array_shift($argReact['order']);
      unset($argReact['_private'][$pId]);
      Log::addAction("react", $argReact);

      // No more players need to react ? => we are over
      if(empty($argReact['order'])){
        $this->gamestate->nextState("finishedReaction");
      } else {
        $pId = $argReact['order'][0];

        // Next player already chose something to react ? => just react then
        if(count($argReact['_private'][$pId]['selection']) > 0){
          self::reactAux($pId, $argReact['_private'][$pId]['selection']);
        }
        else {
          // Otherwise, go back to awaitReaction to make it active
          $this->gamestate->nextState("react");
        }
      }
    }
  }

	function react($ids) {
		$pId = self::getCurrentPlayerId();
 		$player = Players::getPlayer($pId);
		$argReact = $this->argReact();

		if($pId == self::getActivePlayerId()) {
      self::reactAux($pId, $ids);
		} else {
      // Re-made the same pre-choice => unselect it
      if($argReact["_private"][$player->getId()]['selection'] == $ids){
        $this->cancelPreSelection();
      } else {
        // Store the pre-selection and notify it
        $argReact["_private"][$player->getId()]['selection'] = $ids;
        Log::addAction("react", $argReact);
        Notifications::preSelectCards($player, $ids);
      }
		}
 	}

  public function cancelPreSelection(){
    $pId = self::getCurrentPlayerId();
    $player = Players::getPlayer($pId);
    $argReact = $this->argReact();
    $argReact["_private"][$player->getId()]['selection'] = [];
    Log::addAction("react", $argReact);
    Notifications::preSelectCards($player, []);
  }

	public function updateOptions($player, $argReact) {
		$args = Cards::getCurrentCard()->getReactionOptions($player);
		Notifications::updateOptions($player, $args);

		$argReact["_private"][$player->getId()] = $args;
		Log::addAction("react", $argReact);
	}

	public function useAbility($args) {
		$id = self::getCurrentPlayerId();
		Players::getPlayer($id)->useAbility($args);
	}



	public function stEndReaction() {
		$args = Log::getLastAction('react');
		$toEliminate = Players::getPlayersForElimination();

    // Handle direct elimination with no cards in hand
		if(is_null($args)) {
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
