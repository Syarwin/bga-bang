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

$swdNamespaceAutoload = function ($class) {
  $classParts = explode('\\', $class);
  if ($classParts[0] == 'BANG') {
    array_shift($classParts);
    $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
    if (file_exists($file)) {
      require_once $file;
    } else {
      var_dump("Impossible to load bang class : $class");
    }
  }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;
use BANG\Core\Stats;
use BANG\Core\Globals;
use BANG\Core\Log;
use BANG\Core\Stack;

class bang extends Table
{
  use BANG\States\TurnTrait;
  use BANG\States\DrawCardsTrait;
  use BANG\States\PlayCardTrait;
  use BANG\States\ReactTrait;
  use BANG\States\SelectCardTrait;
  use BANG\States\ResolveFlippedTrait;
  use BANG\States\EndOfLifeTrait;
  use BANG\States\EndOfGameTrait;
  use BANG\States\TriggerAbilityTrait;
  use BANG\States\PreferencesTrait;

  public static $instance = null;
  public function __construct()
  {
    parent::__construct();
    self::$instance = $this;
    self::initGameStateLabels([]);
  }
  public static function get()
  {
    return self::$instance;
  }

  protected function getGameName()
  {
    return 'bang';
  }

  /*
   * setupNewGame:
   *  This method is called only once, when a new game is launched.
   * params:
   *  - array $bplayers
   *  - mixed $options
   */
  protected function setupNewGame($bplayers, $options = [])
  {
    // Initialize board and cards
    $expansions = [BASE_GAME];
    Cards::setupNewGame($expansions);

    // Initialize players
    $sheriff = Players::setupNewGame($bplayers, $expansions, $options);
    $this->gamestate->changeActivePlayer($sheriff);
  }

  /*
   * getAllDatas:
   *  Gather all informations about current game situation (visible by the current player).
   *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
   */
  protected function getAllDatas()
  {
    $pId = self::getCurrentPlayerId();
    $result = [
      'players' => Players::getUiData($pId),
      'deck' => Cards::getDeckCount(),
      'discard' => Cards::getLastDiscarded(),
      'playerTurn' => Globals::getPIdTurn(),
      'cards' => Cards::getUIData(),
    ];
    return $result;
  }

  /*
   * getGameProgression:
   *  Compute and return the current game progression approximation
   *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
   */
  public function getGameProgression()
  {
    $bulletsSum = (int) self::getUniqueValueFromDb('SELECT SUM(player_bullets) FROM player');
    $currentHpSum = (int) self::getUniqueValueFromDb('SELECT SUM(player_hp) FROM player');
    $lostBullets = $bulletsSum - $currentHpSum;
    return $lostBullets/$bulletsSum * 100;
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
    $player = Players::get($activePlayer);
    if (!$player->isEliminated()) {
      $atom = Stack::newAtom(ST_ELIMINATE, [
        'type' => 'eliminate',
        'src' => '',
        'attacker' => $activePlayer,
        'pId' => $activePlayer,
      ]);
      Stack::insertOnTop($atom);
      Stack::resolve();
    }
    //      throw new BgaVisibleSystemException(
    //        'Zombie player ' . $activePlayer . ' stuck in unexpected state ' . $state['name']
    //      );
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

  /////////////////////////////////////////////////////////////
  // Exposing protected methods, please use at your own risk //
  /////////////////////////////////////////////////////////////

  // Exposing protected method getCurrentPlayerId
  public static function getCurrentPId()
  {
    return self::getCurrentPlayerId();
  }

  // Exposing protected method translation
  public static function translate($text)
  {
    return self::_($text);
  }
}
