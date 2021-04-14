<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Core\Globals;
use BANG\Managers\Cards;
use BANG\Managers\Players;

trait ResolveFlippedTrait
{
  /*
   * stFlipCard: add a node to flip card, to ensure multi-flip don't overlap
   */
  public function stFlipCard()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    $src = Cards::get($atom['src']['id']);
    Stack::shift();
    $player->flip($src);
    Stack::resolve();
  }

  /*
   * stResolveFlipped: called during the beginning each player turn if Dynamite/Jail required resolving
   */
  public function stResolveFlipped()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);

    $srcCard = Cards::get($atom['src']['id']);
    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($flippedCards->count() == 1) {
      $srcCard->resolveFlipped($flippedCards->first(), $player);
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's {$flippedCards->count()} card in LOCATION_FLIPPED");
    }
  }
}
