<?php
namespace BANG\Managers;
use BANG\Core\Globals;
use BANG\Helpers\DB_Manager;
use BANG\Models\AbstractCard;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

/*
 * Rules: all turn rules and all changes to them according to player role and event and all other factors
 */
class Rules extends DB_Manager
{
  protected static $table = 'rules';
  protected static $primary = 'id';

  /*
   * get: common method for getting a specific rule from the list
   */
  private static function getRule($rule)
  {
    $ruleRow = self::DB()
      ->orderBy(self::$primary, 'DESC')
      ->select($rule)
      ->getSingle();
    if ($ruleRow === null) {
      return null;
    } else {
      return $ruleRow[$rule];
    }
  }

  private static function get()
  {
    $row = self::DB()
      ->orderBy(self::$primary, 'DESC')
      ->getSingle();
    unset($row['id']);
    return $row;
  }

  public static function amendRules($newRules)
  {
    $rules = self::get();
    $rules = array_merge($rules, $newRules);
    self::DB()->insert($rules);
  }

  public static function incrementPhaseOneDrawEndAmount($amount = 1)
  {
    $oldPhaseOneDrawEnd = self::getRule(RULE_PHASE_ONE_CARDS_DRAW_END);
    if ($amount > 0) {
      Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_END => $oldPhaseOneDrawEnd + $amount]);
    }
  }

  /**
   * @param Player $player
   * @param AbstractEventCard $eventCard
   */
  public static function setNewTurnRules($player, $eventCard = null)
  {
    $amountOfCardsToDraw = $eventCard ? $eventCard->getPhaseOneAmountOfCardsToDraw() : 2;
    $defaultRules = (new AbstractEventCard())->getRules();
    $rules = $eventCard ? $eventCard->getRules() : $defaultRules;
    $rules = array_merge($rules, $player->getPhaseOneRules($amountOfCardsToDraw, $rules[RULE_ABILITY_AVAILABLE] ?? true));
    $rules = array_merge(['player_id' => $player->getId()], $rules);
    $query = array_map(function ($value) {
      return is_bool($value) ? (int) $value : $value;
    }, $rules);
    self::DB()->insert($query);
  }

  public static function getPhaseOneCardsAmount($rule)
  {
    if (!in_array($rule, [RULE_PHASE_ONE_CARDS_DRAW_BEGINNING, RULE_PHASE_ONE_CARDS_DRAW_END])) {
      throw new \BgaVisibleSystemException("getPhaseOneCardsAmount - Unexpected rule: $rule");
    }
    return (int) self::getRule($rule);
  }

  public static function isPhaseOnePlayerSpecialDraw()
  {
    return self::getRule(RULE_PHASE_ONE_PLAYER_ABILITY_DRAW) === '1';
  }

  /**
   * @return boolean
   */
  public static function isPhaseOneEventSpecialDraw()
  {
    $eventCard = EventCards::getActive();
    return $eventCard && $eventCard->isPhaseOneSpecialDraw();
  }

  public static function getCurrentPlayerId()
  {
    $current = self::get();
    // backward compatibility from 15/05/2023
    // Change Globals::getPidTurn() to null
    return $current ? (int) $current['player_id'] : Globals::getPIdTurn();
  }

  public static function isAbilityAvailable()
  {
    return self::getRule(RULE_ABILITY_AVAILABLE) === '1';
  }

  public static function isBeerAvailable()
  {
    return (int) self::getRule(RULE_BEER_AVAILABLE) === 1;
  }

  public static function getBangsAmountLeft()
  {
    return (int) self::getRule(RULE_BANGS_AMOUNT_LEFT);
  }

  /**
   * @param string $requestedLocation
   * @return string
   */
  public static function getDrawOrDiscardCardsLocation($requestedLocation)
  {
    $eventCard = EventCards::getActive();
    return $eventCard ? $eventCard->getDrawCardsLocation($requestedLocation) : $requestedLocation;
  }

  public static function bangPlayed()
  {
    $oldAmount = self::getBangsAmountLeft();
    if ($oldAmount > 0) {
      Rules::amendRules([RULE_BANGS_AMOUNT_LEFT => --$oldAmount]);
    }
  }

  /**
   * @return boolean
   */
  public static function isDistanceForcedToOne()
  {
    $eventCard = EventCards::getActive();
    return $eventCard && $eventCard->isDistanceForcedToOne();
  }

  /**
   * @return boolean
   */
  public static function isAimingCards()
  {
    $eventCard = EventCards::getActive();
    return $eventCard && $eventCard->isAimingCards();
  }

  /**
   * @return boolean
   */
  public static function isBangStrictlyForbidden()
  {
    $activeEvent = EventCards::getActive();
    return $activeEvent && $activeEvent->isBangStrictlyForbidden();
  }

  /**
   * @return boolean
   */
  public static function isBangCouldBePlayedWithAnotherBang()
  {
    $activeEvent = EventCards::getActive();
    return $activeEvent && $activeEvent->isBangCouldBePlayedWithAnotherBang();
  }

  /**
   * This method returns info about the flip of a card for some effect (i.e. Barrel,Dynamite,BlackJack...) that can be
   * modified by an active event (i.e. Curse,Blessing).
   *
   * @param AbstractCard $card The card flipped for the effect
   * @param string|array $flipSuccessSuit The suit(s) that a specific effect requires to activate
   * @return array Containing 'flipSuccessful' and 'eventChangedResult' keys. 'eventChangedResult' can be either false or
   *               an array containing 'event' and 'eventSuitOverride'.
   */
  public static function getSuitOverrideInfo($card, $flipSuccessSuit)
  {
    if (!is_array($flipSuccessSuit)) $flipSuccessSuit = [$flipSuccessSuit];

    $cardSuit = $card->getSuit();
    $event = EventCards::getActive();
    $suitOverride = isset($event) ? $event->getSuitOverride() : null;
    $flipSuccessful = in_array($suitOverride ?? $cardSuit, $flipSuccessSuit);

    $eventChangedResult = isset($suitOverride) && $cardSuit != $suitOverride
      && (in_array($cardSuit, $flipSuccessSuit) xor in_array($suitOverride, $flipSuccessSuit));

    if ($eventChangedResult) {
      $eventChangedResult = [
        'eventName' => $event->getName(),
        'eventSuitOverride' => $suitOverride,
      ];
    }

    return [
      'flipSuccessful' => $flipSuccessful,
      'eventChangedResult' => $eventChangedResult,
    ];
  }

  /**
   * @param int | null $exceptId
   * @return boolean
   */
  public static function isIgnoreCardsInPlay($exceptId = null) {
    // $exceptId would be used for Belle Star later
    $eventCard = EventCards::getActive();
    return $eventCard && $eventCard->isIgnoreCardsInPlay();
  }
}
