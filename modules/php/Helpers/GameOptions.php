<?php
namespace BANG\Helpers;

use bang;

class GameOptions
{
  public static function chooseCharactersManually()
  {
    return (int) bang::get()->getGameStateValue('optionCharacters') === CHARACTERS_CHOOSE;
  }
}
