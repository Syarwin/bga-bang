<?php
namespace BANG\Managers;
use bang;
use BANG\Core\Log;

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

  public function setupNewGame($players, $expansions, $options)
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
    ]);

    // Compute roles and shuffle them
    $roles = array_slice([SHERIFF, OUTLAW, OUTLAW, RENEGADE, DEPUTY, OUTLAW, DEPUTY], 0, count($players));
    shuffle($roles);

    // TODO : remove before beta
    $characters = self::getAvailableCharacters($expansions);
    $forcedCharacters = [
      $options[OPTION_CHAR_1],
      $options[OPTION_CHAR_2],
      $options[OPTION_CHAR_3],
      $options[OPTION_CHAR_4],
      $options[OPTION_CHAR_5],
      $options[OPTION_CHAR_6],
      $options[OPTION_CHAR_7],
    ];
    $characters = array_diff($characters, $forcedCharacters);
    shuffle($characters);

    $values = [];
    $i = 0;
    foreach ($players as $pId => $player) {
      $color = $gameInfos['player_colors'][$i];
      $canal = $player['player_canal'];
      $name = $player['player_name'];
      $avatar = addslashes($player['player_avatar']);
      $name = addslashes($player['player_name']);
      $role = $roles[$i];
      $cId = $forcedCharacters[$i] == 100 ? array_pop($characters) : $forcedCharacters[$i];
      $bullets = self::getCharacterBullets($cId);
      if ($role == SHERIFF) {
        $bullets++;
        $sheriff = $pId;
      }
      $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, $bullets, $role, $cId];
      Cards::deal($pId, $bullets);
      $i++;
    }
    $query->values($values);
    self::getGame()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    self::getGame()->reloadPlayersBasicInfos();

    // TODO : remove
    if (false) {
      Cards::dealCard($sheriff, CARD_BARREL);
      Cards::dealCard($sheriff, CARD_INDIANS, 1);
      /*
       Cards::dealCard($sheriff, CARD_INDIANS);
       Cards::dealCard($sheriff, CARD_GATLING);
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
  public function getActiveId()
  {
    return self::getGame()->getActivePlayerId();
  }

  public function getCurrentId()
  {
    return self::getGame()->getCurrentPId();
  }

  public function getAll()
  {
    return self::DB()->get(false);
  }

  public function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public function getActive()
  {
    return self::get();
  }

  public function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public function count()
  {
    return self::DB()->count();
  }

  public function getUiData($pId)
  {
    return self::getAll()->assocMap(function ($player) use ($pId) {
      return $player->getUiData($pId);
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

  public function getCharacter($cId, $row = null)
  {
    $className = 'BANG\Characters\\' . self::$classes[$cId];
    return new $className($row);
  }

  public function getCharacterBullets($cId)
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

  /*******************
   ******* TODO *******
   ********************/

  /*
	public static function getCurrentTurn($asObject = false){
		$playerId = Log::getPlayerTurn();
		return $asObject? self::getPlayer($playerId) : $playerId;
	}


	public static function getSherrifId() {
		return self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_role=" . SHERIFF);
	}
*/

  /*
	 * returns an array with positions of all living players . the values won't always be the same as player_no, but a complete sequence [0,#numberOflivingplayers)
	 *
	public static function getPlayerPositions() {
		return array_flip(self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no", true));
	}*/

  /**
   * returns an array of the ids of all living players
   */
  /*
	public static function getLivingPlayers($except = null, $asPlayerObjects = false) {
		$sql = "SELECT player_id FROM player WHERE player_eliminated = 0";
		if($except != null){
			$ids = is_array($except)? $except : [$except];
			$sql .= " AND player_id NOT IN ('" . implode("','", $ids) . "')";
		}
		$sql .= " ORDER BY player_no";
		$ids = self::getObjectListFromDB($sql, true);
		return $asPlayerObjects ? self::getPlayers($ids) : $ids;
	}

	public static function getLivingPlayersStartingWith($player, $except = null) {
		$and = "";
		if($except != null){
			$ids = is_array($except)? $except : [$except];
			$and = " AND player_id NOT IN ('" . implode("','", $ids) . "')";
		}
		return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_eliminated = 0$and ORDER BY player_no < {$player->getNo()}, player_no", true);
	}


	public static function getPlayersForElimination($asObjects=false) {
		$ids = self::getObjectListFromDB("SELECT player_id FROM player WHERE player_eliminated = 0 AND player_hp <= 0", true);
		return $asObjects ? self::getPlayers($ids) : $ids;
	}


	public static function getEliminatedPlayers() {
		$sql = "SELECT player_id id, player_role role FROM player WHERE player_eliminated = 1";
		return array_values(self::getObjectListFromDB($sql));
	}


  public static function getNextPlayer($player) {
    $players = self::getLivingPlayersStartingWith($player);
		return self::getPlayer($players[1]);
  }



	public static function preparePlayerActivation($playerIds) {
		self::DbQuery("UPDATE player SET player_activate=0");
		$ids = implode(",", $playerIds);
		self::DbQuery("UPDATE player SET player_activate=1 WHERE player_id in($ids)");
	}

	public static function getPlayersForActivation() {
		return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_activate=1", true);
	}

	public static function getTarget() {
		return Log::getLastAction("target")["target"];
	}



	public static function setWinners($winningRoles) {
		$roles = "(" . implode($winningRoles, ",")  . ")";
		self::DbQuery("UPDATE player SET player_score = 1 WHERE player_role in $roles");
		self::DbQuery("UPDATE player SET player_score = 0 WHERE player_role not in $roles");
	}

	public static function handleRemainingEffects() {
		$actions = Log::getActionsAfter("registerAbility", "handledAbilities");
		foreach($actions as $ability) {
			$player = self::getPlayer($ability['id']);
			if(!$player->isEliminated()) $player->useAbility($ability['args']);
		}
		Log::addAction("handledAbilities");
	}
  */
}
