<?php
namespace BANG\Managers;
use BANG\Helpers\Collection;
use bang;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */
class Players extends \BANG\Helpers\DB_Manager
{
  protected static function getGame()
  {
    return bang::get();
  }
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    $cId = $row['player_character'];
    return self::getCharacter($cId, $row);
  }

  public static function setupNewGame($players, $expansions, $options)
  {
    // Create players
    $gameInfos = self::getGame()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $query = self::DB()->multipleInsert([
      'player_id',
      'player_color',
      'player_canal',
      'player_name',
      'player_avatar',
      'player_bullets',
      'player_hp',
      'player_role',
      'player_character',
      'player_autopick_general_store',
    ]);

    // Compute roles and shuffle them
    $roles = array_slice([SHERIFF, OUTLAW, OUTLAW, RENEGADE, DEPUTY, OUTLAW, DEPUTY], 0, count($players));
    shuffle($roles);

    // Handle forced characters
    $characters = self::getAvailableCharacters($expansions);
    $forcedCharacters = [];
    $optionIds = [
      OPTION_CHAR_1,
      OPTION_CHAR_2,
      OPTION_CHAR_3,
      OPTION_CHAR_4,
      OPTION_CHAR_5,
      OPTION_CHAR_6,
      OPTION_CHAR_7,
    ];
    foreach ($optionIds as $oId) {
      $c = $options[$oId];
      if ($c != 100 && in_array($c, $characters) && !in_array($c, $forcedCharacters)) {
        $forcedCharacters[] = $c;
      }
    }

    // Fill with random characters
    $characters = array_diff($characters, $forcedCharacters);
    shuffle($characters);
    $needed = count($players) - count($forcedCharacters);
    for ($i = 0; $i < $needed; $i++) {
      $forcedCharacters[] = array_pop($characters);
    }
    shuffle($forcedCharacters);

    $values = [];
    $i = 0;
    foreach ($players as $pId => $player) {
      $color = $gameInfos['player_colors'][$i];
      $canal = $player['player_canal'];
      $name = $player['player_name'];
      $avatar = addslashes($player['player_avatar']);
      $name = addslashes($player['player_name']);
      $role = $roles[$i];
      $cId = array_pop($forcedCharacters);
      $bullets = self::getCharacterBullets($cId);
      if ($role == SHERIFF) {
        $bullets++;
        $sheriff = $pId;
      }
      $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, $bullets, $role, $cId, 0];
//            $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, 1, $role, $cId, 0];
      Cards::deal($pId, $bullets);
      bang::get()->initStat('player', 'role', $role, $pId);
      $i++;
    }
    $query->values($values);
    self::getGame()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    self::getGame()->reloadPlayersBasicInfos();

    // TODO : remove
    if (false) {
      /*
      Cards::dealCard($sheriff, CARD_GATLING);
      Cards::dealCard($sheriff, CARD_BARREL);
      Cards::dealCard($sheriff, CARD_INDIANS, 1);
       Cards::dealCard($sheriff, CARD_INDIANS);
       Cards::dealCard($sheriff, CARD_REMINGTON);
       Cards::dealCard($sheriff, CARD_DYNAMITE);
     	//Cards::dealCard($sheriff, CARD_JAIL, 1);
       */
    }

    self::getGame()->reloadPlayersBasicInfos();
    return $sheriff;
  }

  /******************************
   ******* GENERIC GETTERS *******
   ******************************/
  public static function getActiveId()
  {
    return self::getGame()->getActivePlayerId();
  }

  public static function getCurrentId()
  {
    return self::getGame()->getCurrentPId();
  }

  public static function getAll()
  {
    return self::DB()->get(false);
  }

  public static function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function count()
  {
    return self::DB()->count();
  }

  public static function getUiData($pId)
  {
    return self::getAll()->map(function ($player) use ($pId) {
      return $player->getUiData($pId);
    });
  }

  public static function getDistances()
  {
    return self::getLivingPlayers()->map(function ($player) {
      return $player->getDistances();
    });
  }

  /*******************************
   ******* CHARACTERS ASSOC ******
   ******************************/
  /*
   * characterClasses : for each character Id, the corresponding class name
   */
  public static $classes = [
    LUCKY_DUKE => 'LuckyDuke',
    EL_GRINGO => 'ElGringo',
    SID_KETCHUM => 'SidKetchum',
    BART_CASSIDY => 'BartCassidy',
    JOURDONNAIS => 'Jourdonnais',
    PAUL_REGRET => 'PaulRegret',
    BLACK_JACK => 'BlackJack',
    PEDRO_RAMIREZ => 'PedroRamirez',
    SUZY_LAFAYETTE => 'SuzyLafayette',
    KIT_CARLSON => 'KitCarlson',
    VULTURE_SAM => 'VultureSam',
    JESSE_JONES => 'JesseJones',
    CALAMITY_JANET => 'CalamityJanet',
    SLAB_THE_KILLER => 'SlabtheKiller',
    WILLY_THE_KID => 'WillytheKid',
    ROSE_DOOLAN => 'RoseDoolan',
  ];

  public static function getAvailableCharacters($expansions)
  {
    $result = [];
    foreach (self::$classes as $cId => $className) {
      $char = self::getCharacter($cId);
      if ($char->isAvailable($expansions)) {
        $result[] = $cId;
      }
    }
    return $result;
  }

  public static function getCharacter($cId, $row = null)
  {
    $className = 'BANG\Characters\\' . self::$classes[$cId];
    return new $className($row);
  }

  public static function getCharacterBullets($cId)
  {
    $char = self::getCharacter($cId, null);
    return $char->getBullets();
  }

  /****************************
   ******* BASIC GETTERS *******
   ****************************/
  protected static function qFilterLiving()
  {
    return self::DB()->where('player_eliminated', 0);
  }

  public static function countRoles($roles)
  {
    if (!is_array($roles)) {
      $roles = [$roles];
    }
    return self::qFilterLiving()
      ->whereIn('player_role', $roles)
      ->count();
  }

  public static function isEndOfGame()
  {
    return self::countRoles([SHERIFF]) == 0 || self::countRoles([OUTLAW, RENEGADE]) == 0;
  }

  public static function getSherrifId()
  {
    return self::getUniqueValueFromDB('SELECT player_id FROM player WHERE player_role = ' . SHERIFF);
  }

  /*
   * returns an array with positions of all living players . the values won't always be the same as player_no, but a complete sequence [0,#numberOflivingplayers)
   */
  public static function getPlayerPositions()
  {
    return array_flip(
      self::getObjectListFromDB('SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no', true)
    );
  }

  /**
   * returns an array of the ids of all living players
   */
  public static function getLivingPlayers($except = null)
  {
    $query = self::DB()
      ->where('player_eliminated', 0)
      ->orderBy('player_no');
    if ($except != null) {
      $ids = is_array($except) ? $except : [$except];
      $query = $query->whereNotIn('player_id', $ids);
    }
    return $query->get();
  }

  public static function getLivingPlayerIdsStartingWith($player, $except = null)
  {
    $and = '';
    if ($except != null) {
      $ids = is_array($except) ? $except : [$except];
      $and = " AND player_id NOT IN ('" . implode("','", $ids) . "')";
    }
    $playerIds = self::getObjectListFromDB(
      "SELECT player_id FROM player WHERE player_eliminated = 0$and ORDER BY player_no < {$player->getNo()}, player_no",
      true
    );
    return array_map(function ($pId) {
      return (int) $pId;
    }, $playerIds);
  }

  public static function getLivingPlayersStartingWith($player)
  {
    $playerIds = self::getLivingPlayerIdsStartingWith($player);
    return new Collection(array_map(function ($pId) {
      return self::get($pId);
    }, $playerIds));
  }

  public static function getNext($player)
  {
    $players = self::getLivingPlayerIdsStartingWith($player);
    return self::get($players[1]);
  }

  /***********************
   ******* SETTERS *******
   ***********************/
  public static function setWinners($winningRoles)
  {
    self::DB()
      ->update(['player_score' => 1])
      ->whereIn('player_role', $winningRoles)
      ->run();
    self::DB()
      ->update(['player_score' => 0])
      ->whereNotIn('player_role', $winningRoles)
      ->run();
  }
}
