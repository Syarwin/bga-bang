<?php

namespace BANG\Managers;

use BANG\Helpers\DB_Manager;
use BANG\Helpers\GameOptions;
use BANG\Models\Player;
use BANG\Helpers\Collection;
use bang;
use feException;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */
class Players extends DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';

  /** @var bool use for tests only together with $testActiveCard */
  protected static bool $isTest = false;

  /** @var Collection used for tests only */
  protected static Collection $players;

  /**
   * @param Player[] $players
   */
  public static function setPlayersForTest(array $players): void
  {
    self::$isTest = true;
    self::$players = new Collection($players);
  }

  protected static function getGame()
  {
    return bang::get();
  }

  protected static function cast($row)
  {
    if ((int) $row['player_character_chosen'] === 0) {
      return new Player($row);
    } else {
      $cId = $row['player_character'];
      return self::getCharacter($cId, $row);
    }
  }

  /**
   * @param non-empty-array<int, array{player_canal: string, player_name: string, player_avatar: string}> $players
   * @param array $expansions
   * @param array $options
   * @return int|string
   * @throws feException
   */
  public static function setupNewGame(array $players, array $expansions, array $options)
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

    $playersCount = count($players);

    // Compute roles and shuffle them
    $roles = array_slice([SHERIFF, RENEGADE, OUTLAW, OUTLAW, DEPUTY, OUTLAW, DEPUTY], 0, $playersCount);
    shuffle($roles);

    // Handle forced characters
    $characters = self::getAvailableCharacters($expansions);
    $charactersToChoice = [];
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
      if ($c != 100 && in_array($c, $characters) && !in_array($c, $charactersToChoice)) {
        $charactersToChoice[] = (int) $c;
      }
    }

    // Fill with random characters
    $characters = array_diff($characters, $charactersToChoice);
    shuffle($characters);
    $needed = $playersCount * 2 - count($charactersToChoice);
    for ($i = 0; $i < $needed; $i++) {
      $charactersToChoice[] = array_pop($characters);
    }
    // slice extra characters
    $charactersToChoice = array_slice($charactersToChoice, 0, $playersCount * 2);
    // slice first choices
    $firstChoices = array_slice($charactersToChoice, 0, $playersCount);
    shuffle($firstChoices);
    // slice alternative choices
    $secondChoices = array_slice($charactersToChoice, $playersCount);
    shuffle($secondChoices);

    $values = [];
    $i = 0;
    $sheriff = 0;
    foreach ($players as $pId => $player) {
      $color = $gameInfos['player_colors'][$i];
      $canal = $player['player_canal'];
      $avatar = addslashes($player['player_avatar']);
      $name = addslashes($player['player_name']);
      $role = $roles[$i];
      $characterId = array_pop($firstChoices);
      $altCharacterId = array_pop($secondChoices);
      $charChosen = !GameOptions::chooseCharactersManually();
      $bullets = $charChosen ? self::getCharacterBullets($characterId) : null;
      if ($role === SHERIFF) {
        if ($charChosen) {
          $bullets++;
        }
        $sheriff = $pId;
      }
      $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, $bullets, $role, $characterId, $altCharacterId, $charChosen, 0];
      // BangDebug: leave 1 HP on game start
//      $values[] = [$pId, $color, $canal, $name, $avatar, $bullets, 1, $role, $characterId, $altCharacterId, $charChosen, 0];
      if ($charChosen) {
        Cards::deal($pId, $bullets);
      }
      bang::get()->initStat('player', 'role', $role, $pId);
      $i++;
    }
    $query->values($values);
    self::getGame()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    self::getGame()->reloadPlayersBasicInfos();

    self::getGame()->reloadPlayersBasicInfos();
    // BangDebug: on game start Sheriff would be the second player
    // $sheriff = Players::getPreviousId(Players::get($sheriff));
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

  /**
   * @param int|null $pId
   * @return Player
   */
  public static function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  /**
   * @return Player
   */
  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function count(): int
  {
    return self::DB()->count();
  }

  public static function getUiData(int $pId)
  {
    return self::getAll()->map(function (Player $player) use ($pId) {
      return $player->getUiData($pId);
    });
  }

  public static function getDistances()
  {
    return self::getLivingPlayers()->map(function (Player $player) {
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

  /**
   * @param (BASE_GAME|HIGH_NOON|DODGE_CITY|FISTFUL_OF_CARDS)[] $expansions
   * @return int[]
   */
  public static function getAvailableCharacters(array $expansions): array
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

  public static function getCharacter($cId, $row = null): Player
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
    return self::DB()->whereIn('player_unconscious', [0, 2]);
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

  private static function getSheriffId()
  {
    return self::getUniqueValueFromDB('SELECT player_id FROM player WHERE player_role = ' . SHERIFF);
  }

  public static function getSheriff()
  {
    return self::get(self::getSheriffId());
  }

  /*
   * returns an array with positions of all living players . the values won't always be the same as player_no, but a complete sequence [0,#numberOflivingplayers)
   */
  public static function getPlayerPositions()
  {
    return array_flip(
      self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated = 0 AND player_unconscious != 1 ORDER BY player_no", true)
    );
  }

  /**
   * returns Collection of all living players
   */
  public static function getLivingPlayers(?int $exceptId = null): Collection
  {
    if (self::$isTest) {
      return self::$players->filter(function(Player $player) use ($exceptId) {
        return $player->getId() !== $exceptId && !$player->isEliminated() && !$player->isUnconscious();
      });
    }

    $playerIds = self::getLivingPlayerIdsStartingWith(null, false, $exceptId);
    return self::idsArrayToCollection($playerIds);
  }

  /**
   * @param Player|null $player
   * @param bool $includeGhosts
   * @param int|null $exceptId
   * @return array
   */
  public static function getLivingPlayerIdsStartingWith($player, $includeGhosts = false, $exceptId = null): array
  {
    $and = '';
    if ($exceptId !== null) {
      $and = " AND player_id NOT IN ('" . implode("','", [$exceptId]) . "')";
    }
    $orderByPlayer = $player ? "player_no < {$player->getNo()}, " : '';
    $includeGhostsSqlString = $includeGhosts ? '' : ' AND `player_unconscious` != 1';
    $playerIds = self::getObjectListFromDB(
      "SELECT player_id FROM player WHERE player_eliminated = 0{$includeGhostsSqlString}{$and} ORDER BY {$orderByPlayer}player_no",
      true
    );
    return array_map(function ($pId) {
      return (int) $pId;
    }, $playerIds);
  }

  /**
   * @param Player $player
   * @param bool $includeGhosts
   * @param int|null $exceptId
   * @return Collection
   */
  public static function getLivingPlayersStartingWith($player, $includeGhosts = false, $exceptId = null)
  {
    $playerIds = self::getLivingPlayerIdsStartingWith($player, $includeGhosts, $exceptId);
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

  public static function getNextId(Player $player, bool $includeGhosts = false): int
  {
    $playersIds = self::getLivingPlayerIdsStartingWith($player, $includeGhosts);
    // But current player might not be alive already... Let's find them
    $currentIndex = array_search($player->getId(), $playersIds);
    if (!is_int($currentIndex)) {
      $currentIndex = -1;
    }
    return $playersIds[$currentIndex + 1];
  }

  public static function getNext(Player $player, bool $includeGhosts = false): Player
  {
    return self::get(self::getNextId($player, $includeGhosts));
  }

  public static function getPreviousId(Player $player, bool $includeGhosts = false): int
  {
    $players = self::getLivingPlayerIdsStartingWith($player, $includeGhosts);
    return $players[count($players)-1];
  }

  /**
   * Returns a whole list of all players who agreed to Ghost Town/resurrection possibility disclaimer
   * @return array
   */
  public static function getNotAgreedToDisclaimerList(): array
  {
    $notAgreedToDisclaimer = self::getLivingPlayers()->map(function (Player $player) {
      return !$player->isAgreedToDisclaimer();
    });
    return array_keys(array_filter($notAgreedToDisclaimer->toAssoc(), 'strlen'));
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
