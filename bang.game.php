<?php
  /**
	*------
	* BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
	* Bang implementation : © <Your name here> <Your email address here>
	*
	* This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
	* See http://en.boardgamearena.com/#!doc/Studio for more information.
	* -----
	*
	* Bang.game.php
	*
	* This is the main file for your game logic.
	*
	* In this PHP file, you are going to defines the rules of the game.
	*
	*/


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class bang extends Table
{
	public static $instance = null;
	public function __construct() {
		parent::__construct();
		self::$instance = $this;
		self::initGameStateLabels([
//      'optionSetup'  => OPTION_SETUP,
			'JourdonnaisUsedSkill' => 17
		]);
	}

	protected function getGameName() {
		return "bang";
	}


	/*
	 * setupNewGame:
	 *  This method is called only once, when a new game is launched.
	 * params:
	 *  - array $bplayers
	 *  - mixed $options
	 */
	protected function setupNewGame($bplayers, $options = []) {
		// Initialize board and cards
		$expansions = [BASE_GAME];
		BangCardManager::setupNewGame($expansions);

		// Initialize players
		$sheriff = BangPlayerManager::setupNewGame($bplayers, $expansions, $this);
		$this->gamestate->changeActivePlayer($sheriff);
	}

	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas() {
		$result = [
			'bplayers' => BangPlayerManager::getUiData(self::getCurrentPlayerId()),
			'deck' => BangCardManager::getDeckCount(),
			'discard' => BangCardManager::getLastDiscarded(),
			'playerTurn' => BangPlayerManager::getCurrentTurn(),
			'cards' => BangCardManager::getUIData(),
			'eliminated' => BangPlayerManager::getEliminatedPlayers()
		];
		return $result;
	}

	/*
	 * getGameProgression:
	 *  Compute and return the current game progression approximation
	 *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
	 */
	public function getGameProgression() {
		// TODO
//    return count($this->board->getPlacedPieces()) / 100;
		return 0.3;
	}





	//////////////////////////////////////////////////////
	////////////   Next player / Start turn   ////////////
	//////////////////////////////////////////////////////

	/*
	 * stNextPlayer: go to next player
	 */
	public function stNextPlayer() {
		$pId = $this->activeNextPlayer();
		self::giveExtraTime($pId);
		$this->gamestate->nextState('start');
	}


	/*
	 * stStartOfTurn: called at the beggining of each player turn
	 */
	public function stStartOfTurn() {
		BangLog::startTurn();
		$player = BangPlayerManager::getActivePlayer();
		$newState = $player->startOfTurn();
		$this->gamestate->nextState($newState);
	}


	/*
	 * stDrawCards: called after the beggining of each player turn, if the turn was not skipped or if no character's abilities apply
	 */
	public function stDrawCards() {
		$player = BangPlayerManager::getActivePlayer();
		$newState = $player->drawCards(2) ?? "play";
		$this->gamestate->nextState($newState);
	}

	/************************
	 **** drawCard state ****
	 ***********************/

	public function argDrawCard() {
		return [
			'_private' => [
				'active' => ['options' => BangLog::getLastAction('draw')]
			]
		];
	}

	public function draw($selected) {
		$newstate = BangPlayerManager::getActivePlayer()->useAbility(['selected' => $selected]);
		$this->gamestate->nextState($newState ?? "play");
	}

/************************
 **** playCard state ****
 ***********************/
	public function argPlayCards() {
		return [
			'_private' => [
				'active' => BangPlayerManager::getActivePlayer()->getHandOptions()
			]
		];
	}

	public function stPlayCard() {
		$players = BangPlayerManager::getLivingPlayers(null, true);
		$newstate = null;
		foreach($players as $player) {
			if($player->getHp() < 1) {
				$newstate = $player->lostLastLife();
				if($newstate == "react") bang::$instance->gamestate->nextState($newState);
			}
			$player->checkHand();
		}
		if($newstate != null) bang::$instance->gamestate->nextState($newState);
	}

	public function playCard($id, $args) {
		self::checkAction('play');
		if(in_array(Utils::getStateName(), ["react", "multiReact"])){
			$this->react($id);
			return;
		}

		// TODO : add check to see if the card was indeed playable
		// if(!in_array($id, $this->argPlayableCards())) ...
		$newState = BangPlayerManager::getActivePlayer()->playCard($id, $args);
		$this->gamestate->nextState($newState ?? "continuePlaying");
	}

/************************
 **** slectCard state ***
 ***********************/

  public function stPrepareSelection() {
		$args = BangLog::getLastAction("selection");
		$players = $args['players'];

		// No more players left to select card => finish selection state
		if(empty($players))
			return $this->stFinishSelection();

		// Set active next player who need to select a card
		$this->gamestate->changeActivePlayer($players[0]);
		$this->gamestate->nextState('select');
	}


	public function argSelect() {
		$args = BangLog::getLastAction("selection");

		$players = $args['players'];
		$amount = array_count_values($players)[$players[0]]; // Amount of cards = number of occurence of player's id
		$selection = BangCardManager::getSelection();
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
		$args = BangLog::getLastAction("selection");
		$selection = BangCardManager::getSelection();

		// Compute the remeaning cards
		$rest = [];
		foreach($selection['cards'] as $card)
			if(!in_array($card['id'], $ids))
				$rest[] = $card['id'];


		// Compute the remeaning players
		array_shift($args['players']); // TODO : don't work if multiple card selected and other players left. And where would that be the case???


		BangLog::addAction("selection", $args);
		$player = BangPlayerManager::getActivePlayer();
		$newstate = isset($args['card'])? $player->react($ids[0])
							: $player->useAbility(['selected' => $ids, 'rest' => $rest ]);
	  $this->gamestate->nextState($newstate ?? 'select');
	}


	public function stFinishSelection(){
		$selection = BangCardManager::getSelection();
		$player = BangPlayerManager::getCurrentTurn(true);
		if(count($selection['cards']) > 0) {
			$player->useAbility($selection['cards']);
		}
		$this->gamestate->changeActivePlayer($player->getId());
		$this->gamestate->nextState('finish');
	}

/*********************
 **** react state ****
 ********************/

	public function stAwaitReaction() {
		BangCardManager::resetPlayedColumn();
		$pId =  array_keys(BangLog::getLastAction("react"))[0];
		$this->gamestate->changeActivePlayer($pId);
		$this->gamestate->nextState();
	}

	public function argReact() {
	 $card = BangCardManager::getCurrentCard();
	 $options = array_values(BangLog::getLastAction("react"))[0];

	 return [
		 '_private' => [
			 'active' => $options
		 ]
	 ];
	}


	public function stAwaitMultiReaction() {
		BangCardManager::resetPlayedColumn();
		//$players = BangPlayerManager::getTarget();
		$players = array_keys(BangLog::getLastAction("react"));
		$this->gamestate->setPlayersMultiactive($players, 'finishedReaction', true); // This transition should never happens as the targets are non-empty
		$this->gamestate->nextState();
	}

 	public function argMultiReact() {
		$args = BangLog::getLastAction("react");
		
 		return [
 			'_private' => $args
 		];
 	}


	function react($id) {
 		$player = BangPlayerManager::getPlayer(self::getCurrentPlayerId());
 		$newState = $player->react($id) ?? "finishedReaction";

		if($newState == "updateOptions"){
			$args = BangCardManager::getCurrentCard()->getReactionOptions($player);
      BangNotificationManager::updateOptions($player, $args);
		} else {
	    if(Utils::getStateName() == 'multiReact') {
				if(BangPlayerManager::countRoles([SHERIFF]) == 0 || BangPlayerManager::countRoles([OUTLAW, RENEGADE]) == 0) {
					$newState = "endgame";
				}
	      bang::$instance->gamestate->setPlayerNonMultiactive(self::getCurrentPlayerId(), $newState);
	    } else bang::$instance->gamestate->nextState($newState);
		}
 	}

	public function useAbility($args) {
		$id = self::getCurrentPlayerId();
		BangPlayerManager::getPlayer($id)->useAbility($args);
	}



	public function stEndReaction() {
		$this->gamestate->changeActivePlayer(BangPlayerManager::getCurrentTurn());
		bang::$instance->gamestate->nextState("finishedReaction");
	}



/*****************************************
 **** endOfTurn / discardExcess state ****
 ****************************************/
	public function endTurn() {
		$player = BangPlayerManager::getPlayer(self::getCurrentPlayerId());
		$newState = ($player->countCardsInHand() > $player->getHp())? "discardExcess" : "endTurn";
		$this->gamestate->nextState($newState);
 	}


	public function argDiscardExcess(){
		$player = BangPlayerManager::getPlayer(self::getActivePlayerId());
		return [
			'amount' => $player->countCardsInHand() - $player->getHp(),
			'_private' => [
				'active' => $player->getCardsInHand(true),
			]
		];
	}

	public function cancelEndTurn(){
		$this->gamestate->nextState("cancel");
	}


	public function discardExcess($cardIds){
		$cards = array_map(function($id){
			BangCardManager::discardCard($id);
			return BangCardManager::getCard($id);
		}, $cardIds);
		$player = BangPlayerManager::getPlayer(self::getActivePlayerId());
		BangNotificationManager::discardedCards($player, $cards);
		$this->gamestate->nextState("endTurn");
	}

	/*
	 * stEndOfTurn: called at the end of each player turn
	 */
	public function stEndOfTurn() {
		//$this->playerManager->getPlayer()->endOfTurn();
		$this->stCheckEndOfGame();
		$this->gamestate->nextState('next');
	}


	/*
	 * stCheckEndOfGame: check if the game is finished
	 */
	public function stCheckEndOfGame() {
		return false;
	}

/*****************************************
 *************** end of Game *************
 ****************************************/
	public function argGameEnd() {
		$players = BangPlayerManager::getPlayers(null, true);
		$alive = BangPlayerManager::getLivingPlayers();
		$winningRoles = [];
		$sheriffEliminated = BangPlayerManager::countRoles([SHERIFF]) == 0;
		$badGuysEliminated = BangPlayerManager::countRoles([OUTLAW, RENEGADE]) == 0;
		if($sheriffEliminated && $badGuysEliminated) {
			// todo can that happen with indians or gatling?
		} elseif($sheriffEliminated) {
			if(count($alive) == 1 && $alive[0]->getRole() == RENEGADE) $winningRoles = [RENEGADE];
			else $winningRoles = [OUTLAW];
		} else {
			$winningRoles = [SHERIFF, DEPUTY];
		}
		$winners = array_filter(function($row) use ($winningRoles) {return in_array($winningRoles, $row['$role']);});
		return [
			'players' => $players,
			'winners' => $winners
		];
	}

	/*
	 * announceWin: TODO
	 *
	public function announceWin($playerId, $win = true) {
		$bplayers = $win ? $this->playerManager->getTeammates($playerId) : $this->playerManager->getOpponents($playerId);
		if (count($bplayers) == 2) {
			self::notifyAllPlayers('message', clienttranslate('${player_name} and ${player_name2} win!'), [
				'player_name' => $bplayers[0]->getName(),
				'player_name2' => $bplayers[1]->getName(),
			]);
		} else {
			self::notifyAllPlayers('message', clienttranslate('${player_name} wins!'), [
				'player_name' => $bplayers[0]->getName(),
			]);
		}
		self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team = {$bplayers[0]->getTeam()}");
		$this->gamestate->nextState('endgame');
	}
*/


	////////////////////////////////////
	////////////   Zombie   ////////////
	////////////////////////////////////
	/*
	 * zombieTurn:
	 *   This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
	 *   You can do whatever you want in order to make sure the turn of this player ends appropriately
	 */
	public function zombieTurn($state, $activePlayer) {
		if (array_key_exists('zombiePass', $state['transitions'])) {
			$this->playerManager->eliminate();
			$this->gamestate->nextState('zombiePass');
		} else {
			throw new BgaVisibleSystemException('Zombie player ' . $activePlayer . ' stuck in unexpected state ' . $state['name']);
		}
	}

	/////////////////////////////////////
	//////////   DB upgrade   ///////////
	/////////////////////////////////////
	// You don't have to care about this until your game has been published on BGA.
	// Once your game is on BGA, this method is called everytime the system detects a game running with your old Database scheme.
	// In this case, if you change your Database scheme, you just have to apply the needed changes in order to
	//   update the game database and allow the game to continue to run with your new version.
	/////////////////////////////////////
	/*
	 * upgradeTableDb
	 *  - int $from_version : current version of this game database, in numerical form.
	 *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
	 */
	public function upgradeTableDb($from_version) {
	}
}
