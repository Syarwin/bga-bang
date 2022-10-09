<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Stack;
use BANG\Managers\Rules;
use BANG\Managers\EventCards;

trait ReactTrait
{
  public function stReact()
  {
    $player = Players::getActive();
    $args = $this->gamestate->state()['args'];
    $options = $args['_private']['active'];
    $noBarrel = empty($options['cards']);
    $noSpecialAbility = !isset($options['character']) || is_null($options['character']);
    $noCardsInHand = $player->getHand()->empty();
    // Auto pass
    if ($noBarrel && $noSpecialAbility && $noCardsInHand) {
      $this->actPass();
    }

    $activeEvent = EventCards::getActive();
    // This is according to High Noon FAQ Q02. During Sermon players automatically lose a life point in Duel if opponent plays a BANG! card
    if ($activeEvent &&
      $activeEvent->isBangStrictlyForbidden() &&
      Rules::getCurrentPlayerId() === $player->getId() &&
      $args['src']['type'] === CARD_DUEL) {
      Notifications::tell('${player_name} automatically loses in a Duel because of The Sermon event card rules', ['player' => $player]);
      $this->actPass();
    }
  }

  private function actPass()
  {
    $this->actReact(null);
  }

  public function argReact()
  {
    $ctx = Stack::getCtx();
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
//    $ctx = Stack::getCtx();
//    $player->react($ids);
//    if ($ctx['state'] == Stack::top()['state']) { // Dirty hack to support Lucky Duke situation. To be refactored
//      Stack::finishState();
//    }
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
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    if ($this->gamestate->state_id() == ST_REACT_BEER) {
      $this->actReactBeer($ids);
    } else {
      $player->react($ids);
    }
    Stack::finishState();
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
    Stack::finishState();
  }
}
