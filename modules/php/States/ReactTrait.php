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
  public function stReact()
  {
    $player = Players::getActive();
    $args = $this->gamestate->state()['args'];
    $options = $args['_private']['active'];
    $noBarrel = empty($options['cards']);
    $noSpecialAbility = is_null($options['character']);
    $noCardsInHand = $player->getHand()->empty();
    if ($noBarrel && $noSpecialAbility && $noCardsInHand) {
      $this->actPass();
    }
  }

  private function actPass() {
    $this->actReact(null);
  }

  public function argReact()
  {
    $ctx = Globals::getStackCtx();
    $player = Players::getActive();
    if ($ctx['state'] == ST_REACT) {
      $card = Cards::get($ctx['src']['id']);

      $ctx['_private']['active'] = $card->getReactionOptions($player);
      return $ctx;
    } else {
      return null; // This might happen when we shifted ST_REACT out of Stack but BGA for some reasons still wants args for it
    }
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

  /*
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

*/

  public function useAbility($args)
  {
    Players::getCurrent()->useAbility($args);
  }
}
