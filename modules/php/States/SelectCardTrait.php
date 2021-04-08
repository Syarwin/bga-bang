<?php
namespace BANG\States;
use BANG\Core\Globals;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;

// Happens when playing a General Store
trait SelectCardTrait
{
  public function stPrepareSelection()
  {
    $args = Log::getLastAction('selection');
    $players = $args['players'];

    // No more players left to select card => finish selection state
    if (empty($players)) {
      return $this->stFinishSelection();
    }

    // Set active next player who need to select a card
    $this->gamestate->changeActivePlayer($players[0]);
    $this->gamestate->nextState('select');
  }

  public function argSelect()
  {
    $args = Globals::getStackCtx();

    $selection = Cards::getSelection()->toArray();
    $data = [
      'i18n' => ['src'],
      'cards' => [],
      'amount' => count($selection),
      'amountToPick' => $args['amount'],
      'src' => $args['src_name'],
    ];

    if ($args['isPrivate']) {
      // TODO: $selection['id'] should be replaced with something else
      $data['_private'] = [$selection['id'] => ['cards' => $selection['cards']]];
    } else {
      $data['cards'] = $selection;
    }

    return $data;
  }

  public function select($ids)
  {
    // Compute the remaining cards
    $rest = Cards::getSelection()->filter(function ($card) use ($ids) {
      return !in_array($card->getId(), $ids);
    });
    // TODO: $rest was used later in $player->useAbility(['selected' => $ids, 'rest' => $rest]); We might want to restore it later

    Log::addAction('selection');
    $playerId = Globals::getStackCtx()['pId'];
    self::reactAux(Players::get($playerId), $ids);
  }

  public function stFinishSelection()
  {
    $selection = Cards::getSelection();
    $player = Players::getCurrentTurn(true);
    if (count($selection['cards']) > 0) {
      $player->useAbility($selection['cards']);
    }
    $this->gamestate->changeActivePlayer($player->getId());
    $this->gamestate->nextState('finish');
  }
}
