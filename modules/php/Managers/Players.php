<?php
namespace BANG\Managers;
use BANG\Core\Globals;
use BANG\Helpers\GameOptions;
use BANG\Models\Player;
use BANG\Helpers\Collection;
use banghighnoon;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */
class Players extends \BANG\Helpers\DB_Manager
{
  protected static function getGame()
  {
    return banghighnoon::get();
  }
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    // backward compatibilty from 15/10/2022
    if (array_key_exists('player_character_chosen', $row) && (int) $row['player_character_chosen'] === 0) {
      return new Player($row);
    } else {
      $cId = $row['player_character'];
      return self::getCharacter($cId, $row);
    }
  }

  public static function setupNewGame($players, $expansions, $options)
  {
    // Create players
    $gameInfos = self::getGame()->getGameinfos();
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
      'player_alt_character',
      'player_character_chosen',
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
        $forcedCharacters[] = (int) $c;
      }
    }

    // Fill with random characters
    $characters = array_diff($characters, $forcedCharacters);
    shuffle($characters);
    $needed = (GameOptions::chooseCharactersManually() ? count($players) * 2 : count($players)) - count($forcedCharacters);
    for ($i = 0; $i < $needed; $i++) {
      $forcedCharacters[] = array_pop($characters);
    }
    shuffle($forcedCharacters);

    $values = [];
    $i = 0;
    foreach ($players as $pId => $player) {
      $color = $gameInfos['player_colors'][$i];
      $canal = $player['player_canal'];
      $avatar = addslashes($player['player_avatar']);
      $name = addslashes($player['player_name']);
      $role = $roles[$i];
      $characterId = array_pop($forcedCharacters);
      $altCharacterId = GameOptions::chooseCharactersManually() ? array_pop($forcedCharacters) : -1;
      $charChosen = !GameOptions::chooseCharactersManually();
      $bullets = $charChosen ? self::getCharacterBullets($characterId) : null;
      if ($role == SHERIFF) {
        if ($charChosen) {
          $bullets++;
        }
        $sheriff = $pId;
      }
//      $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, $bullets, $role, $characterId, $altCharacterId, $charChosen, 0];
            $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, 1, $role, $characterId, $altCharacterId, $charChosen, 0];
      if ($charChosen) {
        Cards::deal($pId, $bullets);
      }
      banghighnoon::get()->initStat('player', 'role', $role, $pId);
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
    $char = self::getCharacter($cId);
    return $char->getBullets();
  }

  /****************************
   ******* BASIC GETTERS *******
   ****************************/
  protected static function qFilterLiving()
  {
    $eliminatedField = self::getEliminatedField(false);
    return self::DB()->where($eliminatedField, 0);
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
    $eliminatedField = self::getEliminatedField(false);
    return array_flip(
      self::getObjectListFromDB("SELECT player_id from player WHERE $eliminatedField=0 ORDER BY player_no", true)
    );
  }

  /**
   * returns an array of the ids of all living players
   */
  public static function getLivingPlayers($except = null)
  {
    $playerIds = self::getLivingPlayerIdsStartingWith(null, false, $except);
    return self::idsArrayToCollection($playerIds);
  }

  /**
   * @return array
   */
  public static function getLivingPlayerIdsStartingWith($player, $includeGhosts = false, $except = null)
  {
    $and = '';
    if ($except != null) {
      $ids = is_array($except) ? $except : [$except];
      $and = " AND player_id NOT IN ('" . implode("','", $ids) . "')";
    }
    $eliminatedField = self::getEliminatedField($includeGhosts);
    $orderByPlayer = $player ? "player_no < {$player->getNo()}, " : '';
    $playerIds = self::getObjectListFromDB(
      "SELECT player_id FROM player WHERE {$eliminatedField} = 0$and ORDER BY {$orderByPlayer}player_no",
      true
    );
    return array_map(function ($pId) {
      return (int) $pId;
    }, $playerIds);
  }

  /**
   * @param Player $player
   * @return Collection
   */
  public static function getLivingPlayersStartingWith($player)
  {
    $playerIds = self::getLivingPlayerIdsStartingWith($player);
    return self::idsArrayToCollection($playerIds);
  }

  /**
   * @param array $playerIds
   * @return Collection
   */
  private static function idsArrayToCollection($playerIds)
  {
    $playersAssoc = [];
    foreach($playerIds as $playerId) {
      $playersAssoc[$playerId] = self::get($playerId);
    }
    return new Collection($playersAssoc);
  }

  /**
   * @param Player $player
   * @param boolean $includeGhosts
   * @return Player
   */
  public static function getNextId($player, $includeGhosts = false)
  {
    $players = self::getLivingPlayerIdsStartingWith($player, $includeGhosts);
    // But current player might not be alive already... Let's find them
    $currentIndex = array_search($player->getId(), $players);
    if (!is_int($currentIndex)) {
      $currentIndex = -1;
    }
    return $players[$currentIndex + 1];
  }

  /**
   * @param Player $player
   * @param boolean $includeGhosts
   * @return Player
   */
  public static function getNext($player, $includeGhosts = false)
  {
    return self::get(self::getNextId($player, $includeGhosts));
  }

  /**
   * @param Player $player
   * @param boolean $includeGhosts
   * @return Player
   */
  public static function getPreviousId($player, $includeGhosts = false)
  {
    $players = self::getLivingPlayerIdsStartingWith($player, $includeGhosts);
    return $players[count($players)-1];
  }

  /**
   * @param boolean $includeGhosts
   * @return string
   */
  private static function getEliminatedField($includeGhosts)
  {
    // backward compatibility from XX/XX/2022
    $newSchema = self::DbQuery('SHOW COLUMNS FROM `player` LIKE \'player_unconscious\'')->num_rows === 1;
    return $newSchema && !$includeGhosts ? 'player_unconscious' : 'player_eliminated';
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
