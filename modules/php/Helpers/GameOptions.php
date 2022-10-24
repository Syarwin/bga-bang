<?php
namespace BANG\Helpers;

use banghighnoon;

class GameOptions
{
  public static function chooseCharactersManually()
  {
    return (int) banghighnoon::get()->getGameStateValue('optionCharacters') === CHARACTERS_CHOOSE;
  }
}
