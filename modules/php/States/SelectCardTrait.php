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
    $args = Stack::getCtx();

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
    $stackCtx = Stack::getCtx();
    $playerId = $stackCtx['pId'];
    if ($stackCtx['toResolveFlipped'] ?? false) {
      Cards::move($ids, LOCATION_FLIPPED);
      $unselectedCards = Cards::getInLocation(LOCATION_SELECTION);
      foreach ($unselectedCards as $card) {
        Cards::discard($card);
      }
    }
    Players::get($playerId)->react($ids);
    Stack::finishState();
  }

  public function stSelect() {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);

    if (isset($atom['src']['type']) && $atom['src']['type'] == CARD_GENERAL_STORE && $player->isAutoPickGeneralStore()) {
      $cards = Cards::getSelection();
      $cardTypes = array_map(function ($card) {
        return $card->getType();
      }, Cards::getSelection()->toArray());
      $typesAmount = count(array_unique($cardTypes));
      if ($typesAmount == 1) {
        $player->react($cards->first()->getId());
        Stack::finishState();
      }
    }
  }
}
