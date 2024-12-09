<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;
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
    $src = $this->getSrcCard($atom['src']);
    $player->flip($src);
    Stack::finishState();
  }

  /*
   * stResolveFlipped: called if Dynamite/Jail/Barrel required resolving
   */
  public function stResolveFlipped()
  {
    $startingAtom = Stack::top();
    $player = Players::get($startingAtom['pId']);
    $srcCard = $startingAtom['src']['id'] === $startingAtom['pId'] ? $player : $this->getSrcCard($startingAtom['src']);
    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($flippedCards->count() == 1) {
      $srcCard->resolveFlipped($flippedCards->first(), $player);
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's {$flippedCards->count()} card in LOCATION_FLIPPED");
    }

    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    foreach ($flippedCards as $card) {
      Cards::discard($card);
    }
    Stack::finishState();
  }

  private function getSrcCard($src)
  {
    $srcCardIsEvent = $src['type'] >= CARD_BLESSING;
    return $srcCardIsEvent ? EventCards::get($src['id']) : Cards::get($src['id']);
  }
}
