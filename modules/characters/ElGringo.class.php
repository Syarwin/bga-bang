<?php

class ElGringo extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = EL_GRINGO;
    $this->character_name = clienttranslate('El Gringo');
    $this->text  = [
      clienttranslate("Each time he loses a life point due to a card played by another player, he draws a random card from the hands of that player "),

    ];
    $this->bullets = 3;
    parent::__construct($row);
  }


  public function looseLife($attacker = null, $amount = 1) {
		parent::looseLife($attacker, $amount);
		if($attacker instanceof BangPlayer) {
			$card = $attacker->getRandomCardInHand();
      BangCardManager::moveCard($card->getId(), 'hand', $this->getId());
			BangNotificationManager::stoleCard($this, $attacker, $card, false);
		}
	}
}
