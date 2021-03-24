<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Log;


trait DrawCardsTrait
{
  /*
	 * stDrawCards: called after the beggining of each player turn, if the turn was not skipped or if no character's abilities apply
	 */
	public function stDrawCards() {
		$player = Players::getActive();
		$newState = null; // TODO : $player->statePhaseOne();
    if(is_null($newState)){
      $newState = "play";
      $player->drawCards(2);
    }

		$this->gamestate->nextState($newState);
	}


	/************************
	 **** drawCard state ****
	 ***********************/
  // Only happens for specific character that can draw in hand of other player for instance
	public function argDrawCard() {
		return [
			'_private' => [
				'active' => ['options' => Log::getLastAction('draw')]
			]
		];
	}

	public function draw($selected) {
		$newstate = Players::getActivePlayer()->useAbility(['selected' => $selected]);
		$this->gamestate->nextState($newState ?? "play");
	}
}
