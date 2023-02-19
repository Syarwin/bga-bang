<?php
namespace BANG\States;
use BANG\Core\Notifications;
use BANG\Managers\Players;
use BANG\Core\Stack;

trait BloodBrothersTrait
{
  public function argBloodBrothers()
  {
    $activePlayer = Players::getActive();
    $otherPlayers = Players::getLivingPlayersStartingWith($activePlayer, false, $activePlayer->getId());
    $otherPlayersNotOnFullHP = $otherPlayers->filter(function ($player) {
      return $player->getBullets() !== $player->getHp();
    });
    $otherPlayersNotOnFullHPIds = array_keys($otherPlayersNotOnFullHP->toAssoc());
    return [
      '_private' => [
        'active' => [
          'players' => $otherPlayersNotOnFullHPIds,
          ]
      ],
    ];
  }

  /**
   * @param int|null $playerId
   * @return void
   */
  public function stBloodBrothers()
  {
    $activePlayer = Players::getActive();
    if ($activePlayer->getHp() === 1) {
      $msg = clienttranslate('${player_name} cannot use the effect of Blood Brothers event because of having 1 life point');
      Notifications::showMessageToAll($msg, [
        'player' => $activePlayer,
      ]);
      Stack::finishState();
    } else if (count($this->argBloodBrothers()['_private']['active']['players']) === 0) {
      $msg = clienttranslate('${player_name} cannot use the effect of Blood Brothers event because everyone else is at full life points');
      Notifications::showMessageToAll($msg, [
        'player' => $activePlayer,
      ]);
      Stack::finishState();
    }
  }

  /**
   * @param int|null $playerId
   * @return void
   */
  public function actReactBloodBrothers($playerId = null)
  {
    self::checkAction('actReactBloodBrothers');
    if ($playerId) {
      Players::getActive()->loseLife();
      Players::get($playerId)->gainLife();
    }
    Stack::finishState();
  }
}
