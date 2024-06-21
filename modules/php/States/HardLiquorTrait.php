<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Stack;
use BANG\Managers\Rules;

trait HardLiquorTrait
{
  public function argHardLiquor()
  {
    return [
      'options' => [
        clienttranslate('Skip drawing phase and regain 1 life point'), clienttranslate('Draw cards normally')
      ],
    ];
  }

  public function actHardLiquorGainHP()
  {
    self::checkAction('actHardLiquorGainHP');
    Players::getActive()->gainLife();
    Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 0]);
    Stack::finishState();
  }

  public function actDeclineHardLiquor()
  {
    self::checkAction('actDeclineHardLiquor');
    Rules::amendRules(Players::getActive()->getPhaseOneRules(2));
    Stack::finishState();
  }

}
