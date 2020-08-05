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
		self::instance = $this;
		self::initGameStateLabels([
//      'optionSetup'  => OPTION_SETUP,
			'currentRound' => 10,
			'firstPlayer'  => 11,
			'state'			=> 12,
			'currentTurn'  => 13, //id of the player who's turn it is(Not always the active player)
			'currentCard'  => 14, //id of the card that has been played
			'bangPlayed' => 15, // whether a bang has been played this turn
			'target'	=> 16
		]);

		// Initialize logger, board and cards
		$this->log   = new BangLog($this);
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

		// Active first player to play

		self::setGameStateInitialValue('firstPlayer', $sheriff);
		self::setGameStateInitialValue('currentRound', 0);
		$this->gamestate->changeActivePlayer( $sheriff );
	}

	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas() {
		$result = array();
		$currentPlayerId = self::getCurrentPlayerId();
		$result['active'] = self::getActivePlayerId();
		$result['bplayers'] = array_values(BangPlayerManager::getUiData(null, $currentPlayerId)); // id => [hp, max_hp no, name, color, character, powers(character effect), hand(count), cardsInPlay]
		//$result['currentPlayer'] = BangPlayerManager::getUiData([$currentPlayerId], true)[0]; // above + role and 'hand' is an array of cards
		$result['deck'] = BangCardManager::countCards('deck');
		//$result['sheriff'] = BangPlayerManager::getSheriff();
		$result['turn'] = $this->getGameStateValue('currentTurn');
		$result['cards'] = array_values($this->cardManager->getUIData());

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


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////
	function playCard($id, $targets) {
		// check for active cards
		self::checkAction( 'play' );
		$player_id = self::getCurrentPlayerId();
		$char = BangPlayerManager::getPlayer($player_id);
		$char->playCard($id, $targets);
		//$card = BangCardManager::createCard($id);

	}

	function react($id) {
		$player_id = self::getCurrentPlayerId();
		$char = BangPlayerManager::getPlayer($player_id);
		$char->selectOption($id);
	}



	////////////////////////////////////////////////
	////////////   Next player / Win   ////////////
	////////////////////////////////////////////////

	/*
	 * stNextPlayer: go to next player
	 */
	public function stNextPlayer() {
		/*
		$pId = $this->activeNextPlayer();
		self::giveExtraTime($pId);
		if (self::getGamestateValue("firstPlayer") == $pId) {
			$n = (int) self::getGamestateValue('currentRound') + 1;
			self::setGamestateValue("currentRound", $n);
		}
		*/
		$this->gamestate->nextState('start');
	}


	/*
	 * stStartOfTurn: called at the beggining of each player turn
	 */
	public function stStartOfTurn() {
		$this->log->startTurn();
		BangPlayerManager::getPlayer(self::getActivePlayerId())->startOfTurn();
		$this->gamestate->nextState("play");
	}


	/*
	 * stEndOfTurn: called at the end of each player turn
	 */
	public function stEndOfTurn() {
		$this->playerManager->getPlayer()->endOfTurn();
		$this->stCheckEndOfGame();
		$this->gamestate->nextState('next');
	}


	/*
	 * stCheckEndOfGame: check if the game is finished
	 */
	public function stCheckEndOfGame() {
		return false;
	}


	public function awaitReaction() {

		if($this->getGameStateValue('state') == PLAY_CARD) {
			$this->gamestate->changeActivePlayer( $this->getGameStateValue('currentTurn') );
			$this->gamestate->nextState( "finishedReaction" );
		} else { //WAIT_REACTION
			$this->gamestate->changeActivePlayer( $this->getGameStateValue('target'));
			$this->gamestate->nextState( "awaitReaction" );
		}
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
	public function argConfirmTurn() {
		return [
			'_private' => [
				'active' => BangPlayerManager::getPlayer(self::getActivePlayerId())->getHandOptions()
			]
		];
	}


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
