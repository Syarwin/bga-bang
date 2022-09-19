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

  public static function setNewTurnRules($player)
  {
    $playerCharacter = $player->getCharacter();
    switch ($playerCharacter) {
      case BLACK_JACK:
        $rules = [1, true, 0];
        break;
      case JESSE_JONES:
      case PEDRO_RAMIREZ:
        $rules = [0, true, 1];
        break;
      case KIT_CARLSON:
        $rules = [0, true, 0];
        break;
      default:
        $rules = [2, false, 0];
    }
    self::DB()->insert([
      'player_id' => $player->getId(),
      RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => $rules[0],
      RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => $rules[1],
      RULE_PHASE_ONE_CARDS_DRAW_END => $rules[2],
    ]);
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
}
