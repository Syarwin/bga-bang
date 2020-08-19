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
		$player->drawCards(2);
		$this->gamestate->nextState("play");
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


	function playCard($id, $args) {
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


/*********************
 **** react state ****
 ********************/

	public function stAwaitReaction() {
		BangCardManager::resetPlayedColumn();
		$this->gamestate->changeActivePlayer(BangPlayerManager::getTarget());
		$this->gamestate->nextState();
	}

	public function argReact() {
	 $card = BangCardManager::getCurrentCard();
	 return [
		 '_private' => [
			 'active' => $card->getReactionOptions(BangPlayerManager::getActivePlayer())
		 ]
	 ];
	}


	public function stAwaitMultiReaction() {
		BangCardManager::resetPlayedColumn();
		$players = BangPlayerManager::getTarget();
		$this->gamestate->setPlayersMultiactive($players, 'finishedReaction', true); // This transition should never happens as the targets are non-empty
		$this->gamestate->nextState();
	}

 	public function argMultiReact() {
 		$players = $this->gamestate->getActivePlayerList();
		$card = BangCardManager::getCurrentCard();
		$arg = [];
		foreach ($players as $id) {
			$args[$id] = $card->getReactionOptions(BangPlayerManager::getPlayer($id));
		}
 		return [
 			'_private' => $args
 		];
 	}


	function react($id) {
 		$character = BangPlayerManager::getPlayer(self::getCurrentPlayerId());
 		$newState = $character->react($id) ?? "finishedReaction";

		if($newState == "updateOptions"){
			$args = $character->getDefensiveOptions();
      BangNotificationManager::updateOptions($character, $args);
		} else {
	    if(Utils::getStateName() == 'multiReact')
	      bang::$instance->gamestate->setPlayerNonMultiactive(self::getCurrentPlayerId(), $newState);
	    else
	      bang::$instance->gamestate->nextState($newState);
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
			$this->playerManager->eliminate($activePlayer);
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
