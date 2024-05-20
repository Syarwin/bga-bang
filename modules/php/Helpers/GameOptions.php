<?php
namespace BANG\Helpers;

use banghighnoon;

class GameOptions
{
  public static function chooseCharactersManually()
  {
    return (int) banghighnoon::get()->getGameStateValue('optionCharacters') === CHARACTERS_CHOOSE;
  }

  public static function getExpansions()
  {
    switch ((int) banghighnoon::get()->getGameStateValue('optionExpansions')) {
      case OPTION_HIGH_NOON_ONLY:
        return [HIGH_NOON];
      case OPTION_FISTFUL_OF_CARDS_ONLY:
        return [FISTFUL_OF_CARDS];
      case OPTION_HIGH_NOON_AND_FOC:
        return [HIGH_NOON, FISTFUL_OF_CARDS];
      default:
        return [];
    }
  }

  /**
   * Are we playing with events which resurrect players at some point?
   * @return boolean
   */
  public static function isResurrection()
  {
    $highNoonWithGhosts = self::getOption('optionExpansions') === OPTION_HIGH_NOON_ONLY &&
      self::getOption('optionHighNoon') === OPTION_HIGH_NOON_WITH_GHOST_TOWN;
    $fistfulWithGhosts = self::getOption('optionExpansions') === OPTION_FISTFUL_OF_CARDS_ONLY &&
      self::getOption('optionFistful') === OPTION_FISTFUL_OF_CARDS_WITH_DEAD_MAN;
    $bothWithGhosts = self::getOption('optionExpansions') === OPTION_HIGH_NOON_AND_FOC &&
      self::getOption('optionHighNoonAndFistful') === OPTION_BOTH_EVENTS_WITH_GHOSTS;
    return $highNoonWithGhosts || $fistfulWithGhosts || $bothWithGhosts;
  }

  private static function getOption($optionName)
  {
    return (int) banghighnoon::get()->getGameStateValue($optionName);
  }

  /**
   * isEvents: are events enabled for this game?
   * @return string
   */
  public static function isEvents()
  {
    return count(array_intersect([HIGH_NOON, FISTFUL_OF_CARDS], self::getExpansions())) > 0;
  }
}
