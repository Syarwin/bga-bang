<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;

trait ResolveFlippedTrait
{
  /*
   * stResolveFlipped: called during the beginning each player turn if Dynamite/Jail/Barrel required resolving
   */
  public function stResolveFlipped()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);

    $srcCard = Cards::get($atom['srcCardId']);
    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($flippedCards->count() == 1) {
      $srcCard->resolveFlipped($flippedCards->first(), $player);
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's $flippedCards->count() card in LOCATION_FLIPPED");
    }
  }
}
