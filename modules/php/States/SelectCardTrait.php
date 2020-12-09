<?php
namespace Bang\States;
use Bang\Characters\Players;
use Bang\Cards\Cards;
use Bang\Game\Log;

// Happens when drawing a General Store
trait SelectCardTrait
{
  public function stPrepareSelection() {
		$args = Log::getLastAction("selection");
		$players = $args['players'];

		// No more players left to select card => finish selection state
		if(empty($players))
			return $this->stFinishSelection();

		// Set active next player who need to select a card
		$this->gamestate->changeActivePlayer($players[0]);
		$this->gamestate->nextState('select');
	}


	public function argSelect() {
		$args = Log::getLastAction("selection");

		$players = $args['players'];
		$amount = array_count_values($players)[$players[0]]; // Amount of cards = number of occurence of player's id
		$selection = Cards::getSelection();
		$data = [
			'i18n' => ['src'],
			'cards' => [],
			'amount' => count($selection['cards']),
			'amountToPick' => $amount,
			'src' => $args['src']
		];

		if($selection['id'] == PUBLIC_SELECTION)
			$data['cards'] = $selection['cards'];
		else
		 	$data['_private'] = [ $selection['id'] => ['cards' => $selection['cards'] ] ];

		return $data;
	}


	public function select($ids) {
		$args = Log::getLastAction("selection");
		$selection = Cards::getSelection();

		// Compute the remeaning cards
		$rest = [];
		foreach($selection['cards'] as $card)
			if(!in_array($card['id'], $ids))
				$rest[] = $card['id'];


		// Compute the remeaning players
		array_shift($args['players']); // TODO : don't work if multiple card selected and other players left. And where would that be the case???


		Log::addAction("selection", $args);
		$player = Players::getActivePlayer();
		$newstate = isset($args['card'])? $player->react($ids)
							: $player->useAbility(['selected' => $ids, 'rest' => $rest ]);

	  $this->gamestate->nextState($newstate ?? 'select');
	}


	public function stFinishSelection(){
		$selection = Cards::getSelection();
		$player = Players::getCurrentTurn(true);
		if(count($selection['cards']) > 0) {
			$player->useAbility($selection['cards']);
		}
		$this->gamestate->changeActivePlayer($player->getId());
		$this->gamestate->nextState('finish');
	}
}
