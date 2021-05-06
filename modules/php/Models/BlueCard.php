<?php
namespace BANG\Models;
use BANG\Managers\Cards;

/*
 * BlueCard:  class to handle blue cards
 */
class BlueCard extends AbstractCard
{
  public function getColor()
  {
    return BLUE;
  }

  public function isEquipment()
  {
    return true;
  }

  public function getPlayOptions($player)
  {
    foreach ($player->getCardsInPlay() as $card) {
      if ($card->type == $this->type) {
        return null;
      }
    }
    return ['target_type' => TARGET_NONE];
  }

  public function play($player, $args)
  {
    Cards::equip($this->id, $player->getId());
  }
}
