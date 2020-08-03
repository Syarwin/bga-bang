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
require_once("modules/includes.php");

class bang extends Table
{
	public function __construct()
	{
		self::initGameStateLabels([
//      'optionSetup'  => OPTION_SETUP,
			'currentRound' => 10,//CURRENT_ROUND,
			'firstPlayer'  => 11,//FIRST_PLAYER,
		]);

		// Initialize logger, board and cards
		$this->log   			= new BangLog($this);
		$this->cards 			= new BangCardManager($this);
		$this->characters = new BangCharacterManager($this);
		$this->bplayers 	= new BangPlayerManager($this);
	}

	protected function getGameName()
	{
		return "bang";
	}


	/*
	 * setupNewGame:
	 *  This method is called only once, when a new game is launched.
	 * params:
	 *  - array $players
	 *  - mixed $options
	 */
	protected function setupNewGame($players, $options = [])
	{
		// Initialize cards
		$this->cards->setupNewGame([BASE_GAME]);
		$this->characters->setupNewGame([BASE_GAME]);

		// Initialize players
		$this->bplayers->setupNewGame($players);

// TODO : add some comments
		$sheriff = $this->bplayers->getSheriff()->getId();
		self::DbQuery("INSERT INTO game(game_state, game_player, game_bangplayed) VALUES(0,{$sheriff},0)");

		// Active first player to play = sheriff
		self::setGameStateInitialValue('firstPlayer', $sheriff);
		self::setGameStateInitialValue('currentRound', 0);
		$this->gamestate->changeActivePlayer($sheriff);
	}

	/*
	 * getAllDatas:
	 *  Gather all informations about current game situation (visible by the current player).
	 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
	 */
	protected function getAllDatas()
	{
		$currentPlayerId = self::getCurrentPlayerId();
		$data = [
			'bplayers' 	 => $this->bplayers->getUiData($currentPlayerId),
			'cards' 		 => $this->cards->getUiData(),
			'characters' => $this->characters->getUiData(),
			'deck' 			 => $this->cards->getDeckCount(),
//???		$result['turn'] = BangPlayerManager::getPlayerTurn();
		];
/*
		$result['args'] = self::getObjectListFromDB("SELECT game_state, game_text msg, game_options, game_player, game_card card From game")[0];
		$t = str_replace(";",",",$result['args']['game_options']);
		if($result['args']['game_state']==1) {
			$result['args']['targets'] = self::getCollectionFromDB("SELECT player_id, player_name name, player_color color FROM player WHERE player_id in ($t)");
			$result['args']['count'] = count($result['args']['targets'])+1;
		}
*/
		return $data;
	}

	/*
	 * getGameProgression:
	 *  Compute and return the current game progression approximation
	 *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
	 */
	public function getGameProgression()
	{
		// TODO
		return 10;
	}


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////
/*
	function playCard($id) {
		// check for active cards
		self::checkAction( 'play' );
		$player_id = self::getCurrentPlayerId();
		//$card = BangCardManager::createCard($id);
		$card = new CardBang();
		$card->id = $id;
		$res = $card->play($player_id);
		$notifs = $res['notifs'];
		foreach($notifs as $notif) {
			if(isset($notif['recipient']))
				self::notifyPlayer($notif['recipient'], $notif['notif'], $notif['msg'], $notif['args']);
			else
				self::notifyAllPlayers($notif['notif'], $notif['msg'], $notif['args']);
		}
		if(isset($res['nextState'])) $this->gamestate->nextState( $res['nextState'] );
	}

	function selectOption($id) {
		$game = self::getObjectListFromDB("SELECT * FROM game")[0];
		$card = BangCardManager::createCard($game['game_card']);
		$card->id = $game['game_card'];
		$res = $card->react($id, $game, self::getCurrentPlayerId());
		$notifs = $res['notifs'];
		foreach($notifs as $notif) {
			if(isset($notif['recipient']))
				self::notifyPlayer($notif['recipient'], $notif['notif'], $notif['msg'], $notif['args']);
			else
				self::notifyAllPlayers($notif['notif'], $notif['msg'], $notif['args']);
		}
		if(isset($res['nextState'])) $this->gamestate->nextState( $res['nextState'] );
	}



	////////////////////////////////////////////////
	////////////   Next player / Win   ////////////
	////////////////////////////////////////////////

	/*
	 * stNextPlayer: go to next player
	 */
	public function stNextPlayer()
	{
		$pId = $this->activeNextPlayer();
		self::giveExtraTime($pId);
		if (self::getGamestateValue("firstPlayer") == $pId) {
			$n = (int) self::getGamestateValue('currentRound') + 1;
			self::setGamestateValue("currentRound", $n);
		}

		$this->gamestate->nextState('start');
	}


	/*
	 * stStartOfTurn: called at the beggining of each player turn
	 */
	public function stStartOfTurn()
	{
		$this->log->startTurn();

//		$this->playerManager->getPlayer()->endOfTurn();

		// TODO : handle start of turn cards such as prison and dynamtie
		$this->gamestate->nextState("play");
	}


	/*
	 * stEndOfTurn: called at the end of each player turn
	 */
	public function stEndOfTurn()
	{
//		$this->playerManager->getPlayer()->endOfTurn();
		$this->stCheckEndOfGame();
		$this->gamestate->nextState('next');
	}


	/*
	 * stCheckEndOfGame: check if the game is finished
	 */
	public function stCheckEndOfGame()
	{
		return false;
	}


/*
	public function awaitReaction() {
		$game = self::getObjectListFromDB("SELECT * FROM game")[0];
		if($game['game_state'] == PLAY_CARD) {
			$this->gamestate->changeActivePlayer( $game['game_player'] );
			$this->gamestate->nextState( "finishedReaction" );
		} else { //WAIT_REACTION
			$this->gamestate->changeActivePlayer( $game['game_target'] );
			$this->gamestate->nextState( "awaitReaction" );
		}
	}
	*/


	public function argPlayCard()
	{
		return [];
	}

	////////////////////////////////////
	////////////   Zombie   ////////////
	////////////////////////////////////
	/*
	 * zombieTurn:
	 *   This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
	 *   You can do whatever you want in order to make sure the turn of this player ends appropriately
	 */
	public function zombieTurn($state, $activePlayer)
	{
		if (array_key_exists('zombiePass', $state['transitions'])) {
			$this->bplayer->eliminate($activePlayer);
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
