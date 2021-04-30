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
        Players::getCurrent()->setGeneralStorePref($value);
        Notifications::showMessage((int) self::getCurrentPId(), toTranslate('Preference is successfully updated'));
        break;
      default:
        throw new \BgaVisibleSystemException("Class PreferencesTrait: unexpected preference '$pref' with value '$value'");
    }
  }
}
