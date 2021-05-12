<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Helpers\Utils;
use BANG\Core\Stack;

trait PlayCardTrait
{
  public function argPlayCards()
  {
    return [
      '_private' => [
        'active' => Players::getActive()->getHandOptions(),
      ],
    ];
  }

  public function stPlayCard()
  {
    /*
		// TODO: Do we need this?
		$players = Players::getLivingPlayers(null, true);
		$newstate = null;
		foreach($players as $player) {
			$player->checkHand();
		}
		if($newstate != null) $this->gamestate->nextState($newState);
    */
  }

  public function actPlayCard($cardId, $args)
  {
    self::checkAction('actPlayCard');
    if (in_array(Utils::getStateName(), ['react', 'multiReact'])) {
      $this->react($cardId);
      return;
    }

    $cardIds = array_map(function ($card) {
      return $card['id'];
    }, $this->argPlayCards()['_private']['active']['cards']);
    if (!in_array($cardId, $cardIds)) {
      throw new BgaVisibleSystemException('You cannot play this card!');
    }

    $card = Cards::get($cardId);
    $player = Players::getActive();
    $player->playCard($card, $args);

    // TODO : not sure what this function was doing before
    //  Players::handleRemainingEffects();
    Stack::finishState();
  }
}
