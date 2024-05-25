<?php
namespace BANG\Managers;

use BANG\Core\Globals;
use BANG\Helpers\GameOptions;
use BANG\Models\AbstractEventCard;

/*
 * Cards: all utility functions concerning cards are here
 */
class EventCards extends \BANG\Helpers\Pieces
{
  protected static $table = 'events';
  protected static $prefix = 'card_';
  protected static $customFields = ['type'];
  protected static function cast($card)
  {
    return self::getCardByType($card['type'], $card);
  }

  public static function setupNewGame($expansions)
  {
    $cards = [];
    foreach (self::$classes as $type => $name) {
      $card = self::getCardByType($type);
      if (!$card->isResurrectionEffect() || GameOptions::isResurrection()) {
        if (in_array($card->getExpansion(), $expansions) && !$card->isLastCard()) {
          $cards[] = [
            'type' => $type,
          ];
        }
      }
    }
    Globals::setResurrectionIsPossible(GameOptions::isResurrection());

    shuffle($cards);
    if (count(self::getCurrentExpansionsIntersesction($expansions)) === 2) {
      $cards = array_slice($cards, 0, 12);
    }

    self::create($cards, LOCATION_EVENTS_DECK);
    $lastCard = self::singleCreate(self::getCurrentExpansionLastCardType($expansions), LOCATION_EVENTS_DECK);
    EventCards::insertAtBottom($lastCard, LOCATION_EVENTS_DECK);
    // TODO: Remove next 2 lines, this is just for debugging purposes
    $lastCard = self::singleCreate(CARD_ABANDONED_MINE, LOCATION_EVENTS_DECK);
    EventCards::insertOnTop($lastCard, LOCATION_EVENTS_DECK);
  }

  /***************************
   ******* CARDS ASSOC *******
   **************************/

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    // High Noon
    CARD_BLESSING => 'Blessing',
    CARD_CURSE => 'Curse',
    CARD_DALTONS => 'Daltons',
    CARD_DOCTOR => 'Doctor',
    CARD_GHOST_TOWN => 'GhostTown',
    CARD_GOLD_RUSH => 'GoldRush',
    CARD_HANGOVER => 'Hangover',
    CARD_REVEREND => 'Reverend',
    CARD_SERMON => 'Sermon',
    CARD_SHOOTOUT => 'Shootout',
    CARD_THIRST => 'Thirst',
    CARD_TRAIN_ARRIVAL => 'TrainArrival',
    CARD_HIGH_NOON => 'HighNoon',

    // A Fistful Of Cards
    CARD_RANCH => 'Ranch',
    CARD_ABANDONED_MINE => 'AbandonedMine',
    CARD_VENDETTA => 'Vendetta',
    CARD_SNIPER => 'Sniper',
    CARD_HARD_LIQUOR => 'HardLiquor',
    CARD_PEYOTE => 'Peyote',
    CARD_AMBUSH => 'Ambush',
    CARD_RICOCHET => 'Ricochet',
    CARD_JUDGE => 'Judge',
    CARD_LASSO => 'Lasso',
    CARD_BLOOD_BROTHERS => 'BloodBrothers',
    CARD_DEAD_MAN => 'DeadMan',
    CARD_FISTFUL_OF_CARDS => 'FistfulOfCards',
    CARD_LAW_OF_THE_WEST => 'LawOfTheWest',
    CARD_RUSSIAN_ROULETTE => 'RussianRoulette',
  ];

  /*
   * getCardByType: factory function to create a card given its type
   */
  public static function getCardByType($cardType, $data = null)
  {
    if (!isset(self::$classes[$cardType])) {
      throw new \BgaVisibleSystemException("getCardByType: Unknown card $cardType");
    }
    $name = 'BANG\Cards\Events\\' . self::$classes[$cardType];
    return new $name($data);
  }

  /*****************************
   ******* BASIC GETTERS *******
   ****************************/
  public static function getAll()
  {
    return array_map(function ($type) {
      return self::getCardByType($type);
    }, array_keys(self::$classes));
  }

  public static function getUiData()
  {
    return array_map(function ($card) {
      return $card->getUiData();
    }, self::getAll());
  }

  /*****************************
   ******* BASIC METHODS *******
   ****************************/
  public static function getDeckCount()
  {
    return self::countInLocation(LOCATION_EVENTS_DECK);
  }

  /**
   * @return AbstractEventCard|null
   */
  public static function getActive()
  {
    return GameOptions::isEvents() ? self::getTopOf(LOCATION_EVENTS_DISCARD) : null;
  }

  /**
   * @return AbstractEventCard|null
   */
  public static function getNext()
  {
    return GameOptions::isEvents() ? self::getTopOf(LOCATION_EVENTS_DECK) : null;
  }

  /**
   * @return AbstractEventCard|null
   */
  public static function next()
  {
    $next = self::getNext();
    if ($next) {
      self::insertOnTop($next->getId(), LOCATION_EVENTS_DISCARD);
    }
    return self::getActive();
  }

  /**
   * @return boolean
   */
  public static function isResurrectionPossible()
  {
    $cards = self::getInLocation(LOCATION_EVENTS_DECK);
    $active = self::getActive();
    if (!is_null($active)) {
      $cards = $cards->push($active);
    }
    $resurrectionCards = $cards->filter(function ($card) {
      return $card->isResurrectionEffect();
    });
    return count($resurrectionCards) > 0;
  }

  /**
   * @param array $expansions
   * @return int
   */
  private static function getCurrentExpansionLastCardType($expansions)
  {
    $currentEventExpansions = self::getCurrentExpansionsIntersesction($expansions);
    switch (count($currentEventExpansions)) {
      case 2:
        return [CARD_HIGH_NOON, CARD_FISTFUL_OF_CARDS][bga_rand(0, 1)];
      case 1:
        $currentExpansion = array_values($currentEventExpansions)[0];
        return [
          HIGH_NOON => CARD_HIGH_NOON,
          FISTFUL_OF_CARDS => CARD_FISTFUL_OF_CARDS,
          ][$currentExpansion];
      default:
        throw new \BgaVisibleSystemException('$currentEventExpansions does not intersect with $eventExpansions, please report as a bug');
    }
  }

  /**
   * @param array $expansions
   * @return array
   */
  private static function getCurrentExpansionsIntersesction($expansions)
  {
    $eventExpansions = [HIGH_NOON, FISTFUL_OF_CARDS];
    return array_intersect($expansions, $eventExpansions);
  }
}
