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

    $players = $args['players'];
    $amount = array_count_values($players)[$players[0]]; // Amount of cards = number of occurence of player's id
    $selection = Cards::getSelection();
    $data = [
      'i18n' => ['src'],
      'cards' => [],
      'amount' => count($selection),
      'amountToPick' => $amount,
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
    $args = Log::getLastAction('selection');
    $cards = Cards::getSelection();

    // Compute the remaining cards
    $rest = array_filter($cards, function($card) use ($ids) {
      return !in_array($card->getId(), $ids);
    });
    // TODO: $rest was used later in $player->useAbility(['selected' => $ids, 'rest' => $rest]); We might want to restore it later

    // Compute the remaining players
    $playerId = array_shift($args['players']); // TODO : don't work if multiple card selected and other players left. And where would that be the case???

    Log::addAction('selection', $args);
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
