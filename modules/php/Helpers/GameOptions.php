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
      case OPTION_BASE_GAME:
        return [];
      case OPTION_HIGH_NOON_ONLY:
        return [HIGH_NOON];
      default:
        return null;
    }
  }

  public static function isEvents()
  {
    return count(array_intersect([HIGH_NOON], self::getExpansions())) > 0; // ...or Fistful of Cards
  }
}
