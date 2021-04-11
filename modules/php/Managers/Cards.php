<?php
namespace BANG\Managers;
use BANG\Core\Log;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Helpers\Utils;

/*
 * Cards: all utility functions concerning cards are here
 */
class Cards extends \BANG\Helpers\Pieces
{
  protected static $table = 'card';
  protected static $prefix = 'card_';
  protected static $customFields = ['type', 'played', 'color', 'value'];
  protected static $autoreshuffle = true;
  protected static function cast($card)
  {
    return self::getCardByType($card['type'], $card);
  }

  public static function setupNewGame($expansions)
  {
    $cards = [];
    foreach (self::$classes as $type => $name) {
      $card = self::getCardByType($type);
      foreach ($expansions as $exp) {
        foreach ($card->getCopies()[$exp] as $copy) {
          $value = Utils::getCopyValue($copy);
          $color = Utils::getCopyColor($copy);

          $cards[] = [
            'type' => $type,
            'value' => $value,
            'color' => $color,
          ];
        }
      }
    }

    self::create($cards, 'deck');
    self::shuffle('deck');
  }

  /***************************
   ******* CARDS ASSOC *******
   **************************/

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    CARD_SCHOFIELD => 'Schofield',
    CARD_VOLCANIC => 'Volcanic',
    CARD_REMINGTON => 'Remington',
    CARD_REV_CARABINE => 'RevCarabine',
    CARD_WINCHESTER => 'Winchester',
    CARD_BANG => 'Bang',
    CARD_MISSED => 'Missed',
    CARD_STAGECOACH => 'Stagecoach',
    CARD_WELLS_FARGO => 'WellsFargo',
    CARD_BEER => 'Beer',
    CARD_GATLING => 'Gatling',
    CARD_PANIC => 'Panic',
    CARD_CAT_BALOU => 'CatBalou',
    CARD_SALOON => 'Saloon',
    CARD_DUEL => 'Duel',
    CARD_GENERAL_STORE => 'GeneralStore',
    CARD_INDIANS => 'Indians',
    CARD_JAIL => 'Jail',
    CARD_DYNAMITE => 'Dynamite',
    CARD_BARREL => 'Barrel',
    CARD_SCOPE => 'Scope',
    CARD_MUSTANG => 'Mustang',
  ];

  /*
   * getCardByType: factory function to create a card given its type
   */
  public static function getCardByType($cardType, $data = null)
  {
    if (!isset(self::$classes[$cardType])) {
      throw new \BgaVisibleSystemException("getCardByType: Unknown card $cardType");
    }
    $name = 'BANG\Cards\\' . self::$classes[$cardType];
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
   ******* BASIC METHOD ********
   ****************************/
  public static function deal($pId, $amount, $fromLocation = 'deck')
  {
    return self::pickForLocation($amount, $fromLocation, ['hand', $pId]);
  }

  public static function getHand($pId)
  {
    return self::getInLocation(['hand', $pId]);
  }

  public static function countHand($pId)
  {
    return self::countInLocation(['hand', $pId]);
  }

  public static function getDeckCount()
  {
    return self::countInLocation('deck');
  }

  public static function getLastDiscarded()
  {
    $card = self::getTopOf('discard');
    return $card;
  }

  public static function getInPlay($pId = null)
  {
    return self::getInLocation(['inPlay', $pId ?? '%']);
  }

  public static function play($id)
  {
    self::insertOnTop($id, 'discard');
  }

  public static function discard($mixed)
  {
    $id = is_int($mixed) ? $mixed : $mixed->getId();
    self::play($id);
  }

  public static function discardMany($arr)
  {
    foreach ($arr as $card) {
      self::discard($card);
    }
  }

  public static function equip($cardId, $pId)
  {
    self::move($cardId, ['inPlay', $pId]);
  }

  public static function stole($mixed, $player)
  {
    $cId = is_int($mixed) ? $mixed : $mixed->getId();
    Cards::move($cId, ['hand', $player->getId()]);
  }

  public static function createSelection($nbr, $player = PUBLIC_SELECTION)
  {
    self::moveAllInLocation('selection', 'discard');
    return self::pickForLocation($nbr, 'deck', 'selection');
  }

  public static function getSelection()
  {
    return self::getInLocation('selection');
  }

  // only for testing
  public static function dealCard($player, $type, $playerOffset = 0)
  {
    //$cards = self::getDeck()->getCardsOfType($type);
    if ($playerOffset > 0) {
      $no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id=$player");
      $count = self::getUniqueValueFromDB('SELECT COUNT(*) FROM player');
      $no += $playerOffset;
      if ($no > $count) {
        $no -= $count;
      }
      $player = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no=$no");
    }
    $cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE type=$type AND card_location='deck'", true);
    self::move($cards[0], ['hand', $player]);
  }
}
