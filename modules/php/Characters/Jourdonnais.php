<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Game\Utils;
use Bang\Cards\Cards;
use bang;

class Jourdonnais extends Player {
  public function __construct($row = null)
  {
    $this->character    = JOURDONNAIS;
    $this->character_name = clienttranslate('Jourdonnais');
    $this->text  = [
      clienttranslate("Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed."),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  // TODO replace with log ?
  protected function canUseAbility(){
    return bang::get()->getGameStateValue('JourdonnaisUsedSkill') == 0;
  }

  protected function logUseAbility(){
    bang::get()->setGameStateValue('JourdonnaisUsedSkill', 1);
  }


  public function getDefensiveOptions() {
    $res = parent::getDefensiveOptions();
    $card = Cards::getCurrentCard();
    if($card->getType() == CARD_BANG && $this->canUseAbility())
        $res['character'] = JOURDONNAIS;
    return $res;
  }

  public function useAbility($args) {
    $args = Log::getLastAction('cardPlayed');
    $amount = $args['missedNeeded'] ?? 1;
    $this->logUseAbility();

    // Draw one card
    $card = $this->flip([], $this);
    if ($card->getCopyColor() == 'H') {
      // Success
      Notifications::tell(clienttranslate("Jourdonnais effect was successfull"));

      if($amount == 1) {
        bang::get()->gamestate->nextState("finishedReaction");
        return;
      }
      // Might happen againt Slab the Killer
      else {
        Notifications::tell(clienttranslate('But ${player_name} needs another miss'), ['player_name' => $this->getName()]);
        $amount--;
        $args = Cards::getCurrentCard()->getReactionOptions($this);
        $args['missedNeeded'] = $amount;
        Log::addCardPlayed(Players::getCurrentTurn(true), Cards::getCurrentCard(), $args);
      }
    }
    // Failure
    else {
      Notifications::tell(clienttranslate("Jourdonnais effect failed"));
    }

    $args = $this->getDefensiveOptions();
    Notifications::updateOptions($this, $args);
  }
}
