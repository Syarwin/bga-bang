<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Core\Globals;

trait EndOfGameTrait
{
  /*
   * setWinners : attribute score to players according to end of game trigger
   */
  public function setWinners()
  {
    $living = Players::getLivingPlayers();
    if (Players::countRoles([SHERIFF]) == 0) {

      // That not's really possible, is it ?
      if (count($living) == 0) {
        Players::setWinners([SHERIFF, DEPUTY, OUTLAW, RENEGADE]);
      } elseif (count($living) == 1 && $living->first()->getRole() == RENEGADE) {
        Players::setWinners([RENEGADE]); // TODO : if two renegades (with expansion), only the one left win
        Notifications::tell(clienttranslate('The renegade is the only one left and thus wins this game.'));
      } else {
        Players::setWinners([OUTLAW]);
        Notifications::tell(clienttranslate('The sheriff has been killed and thus the outlaws win this game.'));
      }
    }

    if (Players::countRoles([OUTLAW, RENEGADE]) == 0) {
      Players::setWinners([SHERIFF, DEPUTY]);
      Notifications::tell(
        clienttranslate('All the renegades and outlaws have been killed and thus Sheriff and Deputies win this game.')
      );
    }

    if(count($living) == 1){
      // THAT'S NO LONGER TRUE IF A ZOMBIE PLAYER IS HERE
      // Framework will auto transition to GAME_END, put this flag to ensure we do not transition (error otherwise)
      Globals::setGameIsOver(true);
      Stack::setup([ST_GAME_END]);
    } else {
      // Clear stack and insert ST_GAME_END only
      Stack::setup([ST_GAME_END]);
    }
    Notifications::revealPlayersRolesEndOfGame();
  }
}
