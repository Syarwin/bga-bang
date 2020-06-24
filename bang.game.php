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
require_once('modules/constants.inc.php');
require_once("modules/BangCards.class.php");
require_once("modules/BangLog.class.php");
require_once("modules/BangPlayerManager.class.php");


class bang extends Table
{
  public function __construct()
  {
    parent::__construct();
    self::initGameStateLabels([
//      'optionSetup'  => OPTION_SETUP,
      'currentRound' => CURRENT_ROUND,
      'firstPlayer'  => FIRST_PLAYER,
    ]);

    // Initialize logger, board and cards
    $this->log   = new KingdomBuilderLog($this);
    $this->cards = new KingdomBuilderCards($this);
    $this->playerManager = new KingdomBuilderPlayerManager($this);
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
		// Initialize board and cards
//    $optionSetup = intval(self::getGameStateValue('optionSetup'));
    $this->cards->setupNewGame($players, $optionSetup);

    // Initialize players
    $this->playerManager->setupNewGame($players);

    // Active first player to play
    $pId = $this->activeNextPlayer();
    self::setGameStateInitialValue('firstPlayer', $pId);
    self::setGameStateInitialValue('currentRound', 0);
  }

  /*
   * getAllDatas:
   *  Gather all informations about current game situation (visible by the current player).
   *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
   */
  protected function getAllDatas()
  {
    $currentPlayerId = self::getCurrentPlayerId();
    return [
      'fplayers' => $this->playerManager->getUiData($currentPlayerId),
    ];
  }

  /*
   * getGameProgression:
   *  Compute and return the current game progression approximation
   *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
   */
  public function getGameProgression()
  {
		// TODO
//    return count($this->board->getPlacedPieces()) / 100;
return 0.3;
  }



  ////////////////////////////////////////////////
  ////////////   Next player / Win   ////////////
  ////////////////////////////////////////////////

  /*
   * stNextPlayer: go to next player
   */
  public function stNextPlayer()
  {
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
  public function stStartOfTurn()
  {
    $this->log->startTurn();
    $this->playerManager->getPlayer()->startOfTurn();
    $this->gamestate->nextState("build");
  }


  /*
   * stEndOfTurn: called at the end of each player turn
   */
  public function stEndOfTurn()
  {
    $this->playerManager->getPlayer()->endOfTurn();
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
   * announceWin: TODO
   *
  public function announceWin($playerId, $win = true)
  {
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
  public function zombieTurn($state, $activePlayer)
  {
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
  public function upgradeTableDb($from_version)
  {
  }
}
