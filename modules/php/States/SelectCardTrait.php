<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;

// Happens when playing a General Store
trait SelectCardTrait
{
  public function argSelect()
  {
    $args = Globals::getStackCtx();

    $selection = Cards::getSelection()->toArray();
    if (empty($selection)) {
      return []; // Fix for weird bug after KitCarlson selection, TODO : investigate why we are going through this a second time
    }

    $data = [
      'i18n' => ['src'],
      'cards' => [],
      'amount' => count($selection),
      'amountToPick' => $args['amountToPick'],
      'src' => $args['src_name'],
    ];

    if ($args['isPrivate']) {
      $data['_private'] = [$args['pId'] => ['cards' => $selection]];
    } else {
      $data['cards'] = $selection;
    }

    return $data;
  }

  public function actSelect($ids)
  {
    $stackCtx = Globals::getStackCtx();
    $playerId = $stackCtx['pId'];
    if ($stackCtx['toResolveFlipped']) {
      Cards::move($ids, LOCATION_FLIPPED);
      Cards::moveAllInLocation(LOCATION_SELECTION, DISCARD);
    }
    self::reactAux(Players::get($playerId), $ids);
  }

  public function stSelect() {
    $player = Players::get(Stack::top()['pId']);
    if ($player->getGeneralStorePref()) {
      $cards = Cards::getSelection();
      $cardTypes = array_map(function ($card) {
        return $card->getType();
      }, Cards::getSelection()->toArray());
      $typesAmount = count(array_unique($cardTypes));
      if ($typesAmount == 1) {
        self::reactAux($player, $cards->first()->getId());
      }
    }
  }
}
