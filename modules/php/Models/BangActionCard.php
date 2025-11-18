<?php

declare(strict_types=1);

namespace BANG\Models;

use BANG\Core\Stack;

/*
 * BangActionCard: class to handle cards which have BANG! symbol thus considered BANG! actions
 */
class BangActionCard extends BrownCard
{
  public function react(AbstractCard $card, Player $player): void
  {
    if ($card->getType() !== CARD_BARREL) {
      // Barrel knows how to handle this
      $missedNeeded = Stack::top()['missedNeeded'] - 1;
      if ($missedNeeded > 0) {
        Stack::suspendCtx();
      }
      Stack::updateAttackAtomAfterAction($missedNeeded, $card->getType());
    }
    parent::react($card, $player);
  }
}
