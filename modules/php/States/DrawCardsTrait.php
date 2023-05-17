<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Stack;

trait DrawCardsTrait
{
  /************************
   **** drawCard state ****
   ***********************/
  // Only happens for specific character that can draw in hand of other player for instance
  public function argDrawCard()
  {
    $player = Players::getActive();
    return [
      '_private' => [
        'active' => $player->argDrawCard(),
      ],
    ];
  }

  public function draw($selected)
  {
    Players::getActive()->useAbility(['selected' => $selected]);
    Stack::finishState();
  }
}
