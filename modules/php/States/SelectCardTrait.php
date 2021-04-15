<?php
namespace BANG\States;
use BANG\Core\Globals;
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
    if(empty($selection)){
      return []; // Fix for weird bug after KitCarlson selection, TODO : investigate why we are going through this a second time
    }

    $data = [
      'i18n' => ['src'],
      'cards' => [],
      'amount' => count($selection),
      'amountToPick' => $args['amount'],
      'src' => $args['src_name'],
    ];

    if ($args['isPrivate']) {
      $data['_private'] = [$args['pId'] => ['cards' => $selection]];
    } else {
      $data['cards'] = $selection;
    }

    return $data;
  }

  public function select($ids)
  {
    $stackCtx = Globals::getStackCtx();
    $playerId = $stackCtx['pId'];
    if ($stackCtx['toResolveFlipped']) {
      Cards::move($ids, LOCATION_FLIPPED);
      Cards::moveAllInLocation(LOCATION_SELECTION, DISCARD);
    }
    self::reactAux(Players::get($playerId), $ids);
  }
}
