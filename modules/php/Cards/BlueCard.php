<?php
namespace Bang\Cards;

/*
 * BlueCard:  class to handle blue cards
 */
class BlueCard extends Card
{
	public function getColor()	 { return BLUE; }
	public function isEquipment(){ return true; }


	public function getPlayOptions($player) {
		foreach($player->getCardsInPlay() as $card)
			if($card->type == $this->type)
				return null;
		return ['type' => OPTION_NONE];
 	}


	public function play($player, $args) {
		// If the card is a weapon, make sure to discard existing weapon
		if ($this->effect['type'] == WEAPON) {
			$player->discardWeapon();
		}
		Cards::moveCard($this->id, 'inPlay', $player->getId());
		return null;
	}
}
