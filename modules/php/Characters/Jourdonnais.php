<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;
use bang;

class Jourdonnais extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = JOURDONNAIS;
    $this->character_name = clienttranslate('Jourdonnais');
    $this->text = [clienttranslate('Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  // TODO replace with log ?
  protected function canUseAbility()
  {
    return bang::get()->getGameStateValue('JourdonnaisUsedSkill') == 0;
  }

  protected function logUseAbility()
  {
    bang::get()->setGameStateValue('JourdonnaisUsedSkill', 1);
  }

  public function getDefensiveOptions()
  {
    $res = parent::getDefensiveOptions();
    /*
    TODO
    $card = Cards::getCurrentCard();
    if ($this->canUseAbility()) {
      // Jourdonnais can use his ability on any attack as a Barrel, so I've removed $card->getType() == CARD_BANG &&
      $res['character'] = JOURDONNAIS;
    }
    */
    return $res;
  }

  public function useAbility($args)
  {
    $args = Log::getLastAction('cardPlayed');
    $amount = $args['missedNeeded'] ?? 1;
    $this->logUseAbility();

    // Draw one card
    $card = $this->flip([], $this);
    if ($card->getCopyColor() == 'H') {
      // Success
      Notifications::tell(clienttranslate('Jourdonnais effect was successfull'));

      if ($amount == 1) {
        bang::get()->gamestate->nextState('finishedReaction');
        return;
      }
      // Might happen againt Slab the Killer
      else {
        Notifications::tell(clienttranslate('But ${player_name} needs another miss'), [
          'player_name' => $this->getName(),
        ]);
        $amount--;
        $args = Cards::getCurrentCard()->getReactionOptions($this);
        $args['missedNeeded'] = $amount;
        Log::addCardPlayed(Players::getCurrentTurn(true), Cards::getCurrentCard(), $args);
      }
    }
    // Failure
    else {
      Notifications::tell(clienttranslate('Jourdonnais effect failed'));
    }

    $args = $this->getDefensiveOptions();
    Notifications::updateOptions($this, $args);
  }
}
