<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Notifications;
use BANG\Core\Globals;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Managers\Rules;
use BANG\Models\Player;

class ElGringo extends Player
{
  public function __construct(?array $row = null)
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

  public function loseLife(int $amount = 1): void
  {
    parent::loseLife($amount);
    // There is no need to steal cards if Russian Roulette is active
    $ctx = Stack::getCtx();
    $isRussianRouletteActive = isset($ctx['type']) && $ctx['type'] === REACT_TYPE_RUSSIAN_ROULETTE;
    if (Rules::isAbilityAvailable() && !$isRussianRouletteActive) {
      $attackerId = Rules::getCurrentPlayerId();
      if ($attackerId != $this->id) {
        $attacker = Players::get($attackerId);
        $attackerIndex = Stack::getFirstIndex(['state' => ST_TRIGGER_ABILITY,
          'pId' => $attackerId
        ]);
        // This is for a specific case when El Gringo loses the Duel and needs to get a card from Suzy AFTER she gets it
        if ($attacker->isCharacter(SUZY_LAFAYETTE) && $attackerIndex > -1) {
          Stack::insertAfter(Stack::newAtom(ST_TRIGGER_ABILITY, [
            'pId' => $this->id,
            'amount' => $amount,
          ]), $attackerIndex + 1);
        } else {
          Stack::insertAfterCardResolution(Stack::newAtom(ST_TRIGGER_ABILITY, [
            'pId' => $this->id,
            'amount' => $amount,
          ]));
        }
      }
    }
  }

  public function useAbility(array $ctx): void
  {
    $attacker = Players::get(Rules::getCurrentPlayerId());
    for ($i = 0; $i < $ctx['amount']; $i++) {
      $card = $attacker->getRandomCardInHand(false);
      if ($card === null) {
        return; // No more cards in hand of attacker
      }
      Cards::move($card->getId(), LOCATION_HAND, $this->getId());
      Notifications::stoleCard($this, $attacker, $card, false);
      if ($card->getId() === Globals::getMustPlayCardId()) {
        Globals::setMustPlayCardId(0);
        Globals::setIsMustPlayCard(false);
      }
      $attacker->onChangeHand();
    }
  }
}
