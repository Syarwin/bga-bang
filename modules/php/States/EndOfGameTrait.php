<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Notifications;

trait EndOfGameTrait
{
  /*
   * stPreGameEnd : attribute score to players according to end of game trigger
   */
  public function stPreGameEnd()
  {
    if (Players::countRoles([SHERIFF]) == 0) {
      $living = Players::getLivingPlayers(null, true);

      // That not's really possible, is it ?
      if (count($living) == 0) {
        Players::setWinners([SHERIFF, DEPUTY, OUTLAW, RENEGADE]);
      } elseif (count($living) == 1 && $living[0]->getRole() == RENEGADE) {
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

    $this->gamestate->nextState('');
  }
}
