<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;

trait ResolveFlippedTrait
{
  /*
   * stFlipCard: add a node to flip card, to ensure multi-flip don't overlap
   */
  public function stFlipCard()
  {
    var_dump('stFlipCard() started');
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    $src = Cards::get($atom['src']['id']);
    $player->flip($src);
    var_dump('stFlipCard() gonna finish');
    Stack::finishState();
    var_dump('stFlipCard() finished');
  }

  /*
   * stResolveFlipped: called if Dynamite/Jail/Barrel required resolving
   */
  public function stResolveFlipped()
  {
    var_dump('stResolveFlipped() started');
    $startingAtom = Stack::top();
    $player = Players::get($startingAtom['pId']);
    $srcCard = $startingAtom['src']['id'] == $startingAtom['pId'] ? $player : Cards::get($startingAtom['src']['id']);
    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($flippedCards->count() == 1) {
      $srcCard->resolveFlipped($flippedCards->first(), $player);
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's {$flippedCards->count()} card in LOCATION_FLIPPED");
    }
    Cards::moveAllInLocation(LOCATION_FLIPPED, LOCATION_DISCARD);
    var_dump('stResolveFlipped() gonna finish');
    Stack::finishState();
    var_dump('stResolveFlipped() finished');
  }
}
