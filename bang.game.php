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
	public function __construct() {
		parent::__construct();
		self::initGameStateLabels([
//      'optionSetup'  => OPTION_SETUP,
			'currentRound' => 10,//CURRENT_ROUND,
			'firstPlayer'  => 11,//FIRST_PLAYER,
		]);

		// Initialize logger, board and cards
		$this->log   = new BangLog($this);
		$this->cardManager = new BangCardManager($this);
		$this->playerManager = new BangPlayerManager($this);
	}

	protected function getGameName() {
		return "bang";
	}


	/*
	 * setupNewGame:
	 *  This method is called only once, when a new game is launched.
	 * params:
	 *  - array $players
	 *  - mixed $options
	 */
	protected function setupNewGame($players, $options = []) {
		// Initialize board and cards

		$n = $this->cardManager->setupNewGame([BASE_GAME]);	
		$deck = range(1,$n);
		shuffle($deck);
		$deck = [8,33,7,9,34,1,10,35,2,11,36,3,12,37,4,13,38,5,14,39,6,15,40];
			// Initialize players
			$this->playerManager->setupNewGame($players);
		
		$roles = array_slice(array(0,2,2,3,1,2,1),0,count($players));
		shuffle($roles);
		
		$characters = range(0,15);
		shuffle($characters);
		$i = 0;
		$values = array();
		
		// hand out characters, roles and cards
		$sql = "INSERT INTO playerinfo(id, role, character_id, current_lp, max_lp) VALUES";
		foreach($players as $id => $player) {
			$char_id = $characters[$i];
			$char_name = BangPlayerManager::$classes[$char_id];
			$char  = new $char_name();
			$role = $roles[$i];
			$lp = $char->bullets;
			if($role ==0) {
				$lp++;
				$sheriff = $id;
			}
			$values[] = "($id, $role, $char_id, $lp)";
			$i++;			
			$cards = array_splice($deck,0,$lp);
			self::DbQuery("UPDATE cards SET card_position = $id, card_onHand=1 WHERE id IN (" . implode(",", $cards) . ")");
		}
		self::DbQuery("INSERT INTO playerinfo(id, role, character_id, max_hp) VALUES" . implode(",",$values));
		$sql .= implode(",", $values);

		self::DbQuery("INSERT INTO game(game_state, game_player, game_bangplayed) VALUES(0,$sheriff,0)");
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
		$result['currentID'] = $currentPlayerId;
		$result['players'] = BangPlayerManager::getUiData();
		$result['deck'] = BangCardManager::getDeckCount();
		$result['sheriff'] = BangPlayerManager::getSheriff();
		$result['turn'] = BangPlayerManager::getPlayerTurn();
		$result['hand'] = BangCardManager::getHand($currentPlayerId);
		$result['cardsInPlay'] = BangCardManager::getCardsInPlay();
		
		$result['args'] = self::getObjectListFromDB("SELECT game_state, game_text msg, game_options, game_player, game_card card From game")[0]; 
		$t = str_replace(";",",",$result['args']['game_options']);
		if($result['args']['game_state']==1) {
			$result['args']['targets'] = self::getCollectionFromDB("SELECT player_id, player_name name, player_color color FROM player WHERE player_id in ($t)");
			$result['args']['count'] = count($result['args']['targets'])+1;			
		}
		
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
		$this->playerManager->getPlayer()->startOfTurn();
		$this->gamestate->nextState("build");
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
		$game = self::getObjectListFromDB("SELECT * FROM game")[0];
		if($game['game_state'] == PLAY_CARD) {
			$this->gamestate->changeActivePlayer( $game['game_player'] );
			$this->gamestate->nextState( "finishedReaction" );
		} else { //WAIT_REACTION			
			$this->gamestate->changeActivePlayer( $game['game_target'] );
			$this->gamestate->nextState( "awaitReaction" );
		}
	}
	/*
	 * announceWin: TODO
	 *
	public function announceWin($playerId, $win = true) {
		$players = $win ? $this->playerManager->getTeammates($playerId) : $this->playerManager->getOpponents($playerId);
		if (count($players) == 2) {
			self::notifyAllPlayers('message', clienttranslate('${player_name} and ${player_name2} win!'), [
				'player_name' => $players[0]->getName(),
				'player_name2' => $players[1]->getName(),
			]);
		} else {
			self::notifyAllPlayers('message', clienttranslate('${player_name} wins!'), [
				'player_name' => $players[0]->getName(),
			]);
		}
		self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team = {$players[0]->getTeam()}");
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
