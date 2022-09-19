<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Globals;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Managers\Rules;

class ElGringo extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = EL_GRINGO;
    $this->character_name = clienttranslate('El Gringo');
    $this->text = [
      clienttranslate(
        'Each time he loses a life point due to a card played by another player, he draws a random card from the hands of that player.'
      ),
    ];
    $this->bullets = 3;
    parent::__construct($row);
  }

  public function loseLife($amount = 1)
  {
    parent::loseLife($amount);

    $attackerId = Rules::getCurrentPlayerId();
    if ($attackerId != $this->id) {
      Stack::insertAfterCardResolution(Stack::newAtom(ST_TRIGGER_ABILITY, [
        'pId' => $this->id,
        'amount' => $amount,
      ]));
    }
  }

  public function useAbility($ctx)
  {
    $attacker = Players::get(Rules::getCurrentPlayerId());
    for ($i = 0; $i < $ctx['amount']; $i++) {
      $card = $attacker->getRandomCardInHand(false);
      if ($card === null) {
        return; // No more cards in hand of attacker
      }
      Cards::move($card->getId(), LOCATION_HAND, $this->getId());
      Notifications::stoleCard($this, $attacker, $card, false);
      $attacker->onChangeHand();
    }
  }
}
