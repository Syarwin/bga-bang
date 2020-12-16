<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Cards\Cards;

class ElGringo extends Player {
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


  public function looseLife($amount = 1) {
		$newstate = parent::looseLife($amount);
    $attacker = Players::getCurrentTurn(true);
		if($attacker->id != $this->id) {
      $this->registerAbility();
		}
    return $newstate;
	}

  public function useAbility($args) {
    $attacker = Players::getCurrentTurn(true);
    $card = $attacker->getRandomCardInHand();
    Cards::moveCard($card->getId(), 'hand', $this->getId());
    Notifications::stoleCard($this, $attacker, $card, false);
  }
}
