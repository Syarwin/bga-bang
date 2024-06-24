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
      return;
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

  public function useAbility($args)
  {
    Players::getCurrent()->useAbility($args);
    Stack::finishState();
  }
}
