<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Globals;
use BANG\Managers\Cards;

class ElGringo extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = EL_GRINGO;
    $this->character_name = clienttranslate('El Gringo');
    $this->text = [
      clienttranslate(
        'Each time he loses a life point due to a card played by another player, he draws a random card from the hands of that player '
      ),
    ];
    $this->bullets = 3;
    parent::__construct($row);
  }

  public function loseLife($amount = 1)
  {
    parent::loseLife($amount);

    /*
TODO
    $attacker = Players::getCurrentTurn(true);
    if ($attacker->id != $this->id) {
      $this->registerAbility();
    }
    return $newstate;
*/
  }

  public function useAbility($args)
  {
    $attacker = Players::getCurrentTurn(true);
    $card = $attacker->getRandomCardInHand();
    Cards::move($card->getId(), LOCATION_HAND, $this->getId());
    Notifications::stoleCard($this, $attacker, $card, false);
  }
}
