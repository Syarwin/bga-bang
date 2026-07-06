<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Stack;
use BgaVisibleSystemException;

trait DrawCardsTrait
{
  /************************
   **** drawCard state ****
   **********************
   * Only happens for specific character that can draw in hand of other player for instance
   *
   * @throws BgaVisibleSystemException
   */
  public function argDrawCard(): array
  {
    $player = Players::getActive();
    if (!method_exists($player, 'argDrawCard')) {
      throw new BgaVisibleSystemException('argDrawCard does not exist for character ' . $player->getCharName());
    }
    return [
      '_private' => [
        'active' => $player->argDrawCard(),
      ],
    ];
  }

  public function draw($selected): void
  {
    $player = Players::getActive();
    if (!method_exists($player, 'useAbility')) {
      throw new BgaVisibleSystemException('useAbility does not exist for character ' . $player->getCharName());
    }
    $player->useAbility(['selected' => $selected]);
    Stack::finishState();
  }
}
