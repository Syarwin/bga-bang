<?php
namespace BANG\Managers;

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
      if (in_array($card->getExpansion(), $expansions) && !$card->isLastCard()) {
        $cards[] = [
          'type' => $type,
        ];
      }
    }

    self::create($cards, LOCATION_EVENTS_DECK);
    self::shuffle(LOCATION_EVENTS_DECK);
    // TODO: While implementing A Fistful Of Cards expansion, choose randomly (or make players choose) which of 2 cards would be the last
    $highNoonCard = self::singleCreate(CARD_HIGH_NOON, LOCATION_EVENTS_DECK);
    EventCards::insertAtBottom($highNoonCard, LOCATION_EVENTS_DECK);
  }

  /***************************
   ******* CARDS ASSOC *******
   **************************/

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    CARD_BLESSING => 'Blessing',
    CARD_CURSE => 'Curse',
    CARD_DALTONS => 'Daltons',
    CARD_DOCTOR => 'Doctor',
//    CARD_GHOST_TOWN => 'GhostTown',
    CARD_GOLD_RUSH => 'GoldRush',
    CARD_HANGOVER => 'Hangover',
    CARD_REVEREND => 'Reverend',
    CARD_SERMON => 'Sermon',
    CARD_SHOOTOUT => 'Shootout',
    CARD_THIRST => 'Thirst',
    CARD_TRAIN_ARRIVAL => 'TrainArrival',
    CARD_HIGH_NOON => 'HighNoon',
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
    return self::getTopOf(LOCATION_EVENTS_DISCARD);
  }

  /**
   * @return AbstractEventCard|null
   */
  public static function getNext()
  {
    return self::getTopOf(LOCATION_EVENTS_DECK);
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
}
