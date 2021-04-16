<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;
use BANG\Core\Stats;
use BANG\Core\Log;
use BANG\Core\Globals;
use BANG\Core\Stack;

trait EndOfLifeTrait
{
  /**
   * BEER saving mode
   */
  public function argReactBeer()
  {
    $ctx = Globals::getStackCtx();
    $player = Players::getActive();

    $hand = $player->getHand();
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

    // If it's not enough, add a ELIMINATE node
    if ($player->getHp() <= 0) {
      $ctx = Globals::getStackCtx();
      $atom = [
        'state' => ST_ELIMINATE,
        'type' => 'eliminate',
        'src' => $ctx['src'],
        'attacker' => $ctx['attacker'],
        'pId' => $player->getId(),
      ];
      Stack::insertAfterCardResolution($atom);
    }

    // Resolve current beer state
    Stack::nextState();
  }

  /**
   * Eliminate a player
   */
  public function stEliminate()
  {
    Stack::shift();
    $ctx = Globals::getStackCtx();
    $player = Players::get($ctx['pId']);
    $player->eliminate();

    // Check if game should end
    if (Stack::isItLastElimination() && Players::isEndOfGame()) {
      $atom = [
        'state' => ST_PRE_GAME_END,
      ];
      Stack::insertOnTop($atom);
    }

    Stack::resolve();
  }
}
