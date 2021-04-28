<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Notifications;

trait PreferencesTrait
{
  /*
   * changePreference: changes some preferences for specific player
   */
  public function changePreference($pref, $value)
  {
    switch ($pref) {
      case OPTION_GENERAL_STORE_LAST_CARD:
        $playerId = (int) self::getCurrentPId();
        Players::get($playerId)->setGeneralStorePref($value);
        Notifications::showMessage($playerId, toTranslate('Preference is successfully updated'));
        break;
      default:
        throw new \BgaVisibleSystemException("Class PreferencesTrait: unexpected preference '$pref' with value '$value'");
    }
  }
}
