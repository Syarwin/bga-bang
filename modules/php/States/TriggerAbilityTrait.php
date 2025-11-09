<?php

namespace BANG\States;

use BANG\Core\Stack;
use BANG\Managers\Players;

trait TriggerAbilityTrait
{
  /*
   * stTriggerAbility: node that was added because of some character passive ability
   */
  public function stTriggerAbility()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    if (method_exists($player, 'useAbility')) {
      $player->useAbility($atom);
    }
    Stack::finishState();
  }
}
