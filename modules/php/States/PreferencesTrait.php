<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Notifications;

trait PreferencesTrait
{
  /*
   * changePreference: changes some preferences for specific player
   */
  public function changePreference($playerId, $pref, $value)
  {
    switch ($pref) {
      case OPTION_GENERAL_STORE_LAST_CARD:
        Players::setGeneralStorePref($playerId, $value);
        Notifications::showMessage($playerId, toTranslate('Preference is successfully updated'));
        break;
    }
  }
}
