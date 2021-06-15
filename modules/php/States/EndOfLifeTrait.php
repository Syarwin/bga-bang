<?php
namespace BANG\States;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Core\Stack;

trait EndOfLifeTrait
{
  /**
   * BEER saving mode
   */
  public function argReactBeer()
  {
    $player = Players::getActive();

    $needed = 1 - $player->getHp();
    //  TODO auto kill if not enough cards
    //    if (count($hand) >= $needed) {
    return [
      'n' => $needed,
      '_private' => [
        'active' => $player->getBeerOptions(),
      ],
    ];
  }

  public function actReactBeer($ids)
  {
    // Play the beer cards picked by player
    $player = Players::getActive();
    if ($ids != null) {
      foreach (Cards::getMany($ids) as $card) {
        $player->playCard($card, []);
      }
    }
    $player->eliminateIfOutOfHp();
  }

  /**
   * Eliminate a player
   */
  public function stEliminate()
  {
    $ctx = Stack::getCtx();
    $player = Players::get($ctx['pId']);
    $player->eliminate();
    Stack::finishState();
  }
}
