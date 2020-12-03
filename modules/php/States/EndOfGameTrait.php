<?php
namespace Bang\States;
use Bang\Characters\Players;

trait EndOfGameTrait
{
  /*
   * stCheckEndOfGame: check if the game is finished
   */
  public function stCheckEndOfGame() {
    return false;
  }

 	public function argGameEnd() {
 		$players = Players::getPlayers(null, true);
 		$winners = array_filter($players, function($row) {return $row['score']==1;});
 		return [
 			'players' => $players,
 			'winners' => $winners
 		];
 	}

 	/*
 	 * announceWin: TODO
 	 *
 	public function announceWin($playerId, $win = true) {
 		$bplayers = $win ? $this->playerManager->getTeammates($playerId) : $this->playerManager->getOpponents($playerId);
 		if (count($bplayers) == 2) {
 			self::notifyAllPlayers('message', clienttranslate('${player_name} and ${player_name2} win!'), [
 				'player_name' => $bplayers[0]->getName(),
 				'player_name2' => $bplayers[1]->getName(),
 			]);
 		} else {
 			self::notifyAllPlayers('message', clienttranslate('${player_name} wins!'), [
 				'player_name' => $bplayers[0]->getName(),
 			]);
 		}
 		self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team = {$bplayers[0]->getTeam()}");
 		$this->gamestate->nextState('endgame');
 	}
 */
}
