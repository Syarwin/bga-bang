<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Core\Notifications;

trait ResolveFlippedTrait
{
  /*
   * stResolveFlipped: called during the beginning each player turn if Dynamite or Jail required resolving
   */
  public function stResolveFlipped()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    $args = ['player' => $player];
    $cards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($cards->count() == 1) {
      $card = $cards->first();
      $player->discardCard($card, true); // Discard a flipped card
      $player->discardCard($atom['src'], true); // Discard Jail itself

      if ($card->getCopyColor() == 'H') {
        Notifications::tell('${player_name} can make his turn', $args);
        Stack::nextState();
      } else {
        Notifications::tell('${player_name} is skipped', $args);
        Stack::clearAllLeaveLast();
      }
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's $cards->count() card in LOCATION_FLIPPED");
    }
  }
}
