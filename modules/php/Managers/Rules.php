<?php
namespace BANG\Managers;
use BANG\Helpers\DB_Manager;

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
    return self::DB()
      ->orderBy(self::$primary, 'DESC')
      ->select($rule)
      ->getSingle()[$rule];
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

  public static function setNewTurnRules($player, $eventCard = null)
  {
    $amountOfCardsToDraw = $eventCard ? $eventCard->getPhaseOneAmountOfCardsToDraw() : 2;
    $rules = $player->getPhaseOneRules($amountOfCardsToDraw);
    $query = array_merge(['player_id' => $player->getId()], $rules);
    $query[RULE_PHASE_ONE_PLAYER_ABILITY_DRAW] = (int) $query[RULE_PHASE_ONE_PLAYER_ABILITY_DRAW];
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

  public static function getCurrentPlayerId()
  {
    return (int) self::get()['player_id'];
  }
}
