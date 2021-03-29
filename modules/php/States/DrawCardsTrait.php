<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Log;
use BANG\Core\Stack;


trait DrawCardsTrait
{
  /*
	 * stDrawCards: called after the beggining of each player turn, if the turn was not skipped or if no character's abilities apply
	 */
	public function stDrawCards() {
		$player = Players::getActive();
    $player->drawCards(2);
    Stack::nextState();
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
