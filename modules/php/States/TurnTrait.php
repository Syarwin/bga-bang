<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Log;
use BANG\Core\Notifications;


trait TurnTrait
{
  /*
   * stNextPlayer: go to next player
   */
  public function stNextPlayer() {
    $pId = $this->activeNextPlayer();

    if (Players::getPlayer($pId)->isEliminated()) {
      $this->stNextPlayer();
      return;
    }

    self::giveExtraTime($pId);
    $this->gamestate->nextState('start');
  }


  /*
   * stStartOfTurn: called at the beggining of each player turn
   */
  public function stStartOfTurn()
  {
    Log::startTurn();
    $player = Players::getActive();
    $newState = $player->startOfTurn();
    $this->gamestate->nextState($newState);
  }


  /*****************************************
   **** endOfTurn / discardExcess state ****
   ****************************************/
  	public function endTurn() {
  		$player = Players::getPlayer(self::getCurrentPlayerId());
  		$newState = ($player->countCardsInHand() > $player->getHp())? "discardExcess" : "endTurn";
  		$this->gamestate->nextState($newState);
   	}


  	public function argDiscardExcess(){
  		$player = Players::getPlayer(self::getActivePlayerId());
  		return [
  			'amount' => $player->countCardsInHand() - $player->getHp(),
  			'_private' => [
  				'active' => $player->getCardsInHand(true),
  			]
  		];
  	}

  	public function cancelEndTurn(){
  		$this->gamestate->nextState("cancel");
  	}


  	public function discardExcess($cardIds){
  		$cards = array_map(function($id){
  			Cards::discardCard($id);
  			return Cards::getCard($id);
  		}, $cardIds);
  		$player = Players::getPlayer(self::getActivePlayerId());
  		Notifications::discardedCards($player, $cards);
  		$this->gamestate->nextState("endTurn");
  	}

  	/*
  	 * stEndOfTurn: called at the end of each player turn
  	 */
  	public function stEndOfTurn() {
  		$this->gamestate->nextState('next');
  	}


}
