<?php
namespace BANG\Models;

/*
 * WeaponCard:  class to handle blue weapon cards
 */
class WeaponCard extends BlueCard
{
  public function getPlayOptions(Player $player): ?array
  {
    $options = parent::getPlayOptions($player);
    if ($options != null && $player->getWeapon() != null) {
      $options['confirmationMsg'] = clienttranslate('This weapon will replace the current one. Are you sure?');
    }
    return $options;
  }

  public function isWeapon()
  {
    return true;
  }

  public function play(Player $player, array $args): void
  {
    $player->discardWeapon();
    parent::play($player, $args);
  }
}
