<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;
use BANG\Core\Stats;
use BANG\Core\Log;
use BANG\Core\Globals;
use BANG\Core\Stack;

trait ReactTrait
{
  public function argReact()
  {
    $ctx = Globals::getStackCtx();
    $player = Players::getActive();
    $card = Cards::get($ctx['src']['id']);

    $ctx['_private'][$player->getId()] = $card->getReactionOptions($player);
    return $ctx;
  }

  function reactAux($player, $ids)
  {
    $ctx = Globals::getStackCtx();
    $player->react($ids, $ctx);
    Stack::nextState();
  }

  /*
TODO : handle with stack engine
// Otherwise, reaction is over, proceed to next player if any
else {
  array_shift($argReact['order']);
  unset($argReact['_private'][$pId]);
  Log::addAction('react', $argReact);

  // No more players need to react ? => we are over
  if (empty($argReact['order'])) {
    $this->gamestate->nextState('finishedReaction');
  } else {
    $pId = $argReact['order'][0];

    // Next player already chose something to react ? => just react then
    if (count($argReact['_private'][$pId]['selection']) > 0) {
      self::reactAux($pId, $argReact['_private'][$pId]['selection']);
    } else {
      // Otherwise, go back to awaitReaction to make it active
      $this->gamestate->nextState('react');
    }
  }
}
*/

  function actReact($ids)
  {
    $player = Players::getCurrent();
    Cards::resetPlayedColumn();

    if ($player->getId() == self::getActivePlayerId()) {
      if ($this->gamestate->state_id() == ST_REACT_BEER) {
        $this->actReactBeer($ids);
      } else {
        $this->reactAux($player, $ids);
      }
    } else {
      /*
TODO : preselection stuff
      // Re-made the same pre-choice => unselect it
      if ($argReact['_private'][$player->getId()]['selection'] == $ids) {
        $this->cancelPreSelection();
      } else {
        // Store the pre-selection and notify it
        $argReact['_private'][$player->getId()]['selection'] = $ids;
        Log::addAction('react', $argReact);
        Notifications::preSelectCards($player, $ids);
      }
*/
    }
  }

  public function actCancelPreSelection()
  {
    $pId = self::getCurrentPlayerId();
    $player = Players::getPlayer($pId);
    $argReact = $this->argReact();
    $argReact['_private'][$player->getId()]['selection'] = [];
    Log::addAction('react', $argReact);
    Notifications::preSelectCards($player, []);
  }

  public function updateOptions($player, $argReact)
  {
    $args = Cards::getCurrentCard()->getReactionOptions($player);
    Notifications::updateOptions($player, $args);

    $argReact['_private'][$player->getId()] = $args;
    Log::addAction('react', $argReact);
  }

  public function useAbility($args)
  {
    $id = self::getCurrentPlayerId();
    Players::getPlayer($id)->useAbility($args);
  }

  public function stEndReaction()
  {
    $args = Log::getLastAction('react');
    $toEliminate = Players::getPlayersForElimination();

    // Handle direct elimination with no cards in hand
    if (is_null($args)) {
      // Find the next living player after current player that is not going to be eliminated
      $living = Players::getLivingPlayersStartingWith(Players::getCurrentTurn(true));
      foreach ($living as $id) {
        if (!in_array($id, $toEliminate)) {
          if ($id != self::getActivePlayerId()) {
            $this->gamestate->changeActivePlayer($id);
          }
          break;
        }
      }

      $nextState = array_reduce(
        $toEliminate,
        function ($state, $id) {
          return Players::getPlayer($id)->eliminate() ?? $state;
        },
        null
      );
      if (is_null($nextState)) {
        if (Players::getCurrentTurn(true)->isEliminated()) {
          $nextState = 'next';
        } else {
          $nextState = Log::getLastAction('lastState')[0] == 'startOfTurn' ? 'draw' : 'finishedReaction';
        }
      }
      if ($nextState == 'finishedReaction') {
        Players::handleRemainingEffects();
        Cards::resetPlayedColumn();
      }
      $this->gamestate->nextState($nextState);
      return;
    }

    $this->gamestate->changeActivePlayer(Players::getCurrentTurn());
    if (count($toEliminate) > 0) {
      $this->gamestate->nextState('eliminate');
    } else {
      Players::handleRemainingEffects();
      Cards::resetPlayedColumn();
      $this->gamestate->nextState('finishedReaction');
    }
  }
}
