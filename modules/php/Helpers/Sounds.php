<?php
namespace BANG\Helpers;

abstract class Sounds
{
  static $girls = [SUZY_LAFAYETTE, CALAMITY_JANET, ROSE_DOOLAN];

  /**
   * @param int $cardType
   * @param int $character
   * @return string|null
   */
  public static function getSoundForPlayedCard($cardType, $character)
  {
    switch ($cardType) {
      case CARD_SCHOFIELD:
      case CARD_VOLCANIC:
      case CARD_REMINGTON:
      case CARD_REV_CARABINE:
      case CARD_WINCHESTER:
        return 'weapon';
      case CARD_BANG:
        return 'bang';
      case CARD_MISSED:
        return 'missed';
      case CARD_BEER:
        $num = in_array($character, self::$girls) ? 1 : 2;
        return "beer$num";
      case CARD_GATLING:
        return 'gatling';
      case CARD_DYNAMITE:
      case CARD_BARREL:
      case CARD_SCOPE:
      case CARD_JAIL:
        return 'bluecomes';
      case CARD_MUSTANG:
        return 'mustang';
      default:
        return null;
    }
  }

  /**
   * @return string
   */
  public static function getSoundForLostLife($character)
  {
    $num = in_array($character, self::$girls) ? 1 : 2;
    return "grunt${num}";
  }

  /**
   * @return string
   */
  public static function getSoundForElimination()
  {
    return 'death';
  }

  /**
   * @return string
   */
  public static function getSoundForCharacterAbility()
  {
    return 'ability';
  }

  /**
   * @return string
   */
  public static function getSoundForEndGame()
  {
    return 'endgame';
  }

  /**
   * @return string
   */
  public static function getSoundForStartGame()
  {
    return 'intro';
  }
}
