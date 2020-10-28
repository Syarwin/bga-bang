<?php

class Jourdonnais extends BangPlayer {
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
    return bang::$instance->getGameStateValue('JourdonnaisUsedSkill') == 0;
  }

  protected function logUseAbility(){
    bang::$instance->setGameStateValue('JourdonnaisUsedSkill', 1);
  }


  public function getDefensiveOptions() {
    $res = parent::getDefensiveOptions();
    $card = BangCardManager::getCurrentCard();
    if($card->getType() == CARD_BANG && $this->canUseAbility())
        $res['character'] = JOURDONNAIS;
    return $res;
  }

  public function useAbility($args) {
    $args = BangLog::getLastAction('cardPlayed');
    $amount = $args['missedNeeded'] ?? 1;
    $this->logUseAbility();

    // Draw one card
    $card = $this->draw([], $this);
    if ($card->getCopyColor() == 'H') {
      // Success
      BangNotificationManager::tell(clienttranslate("Jourdonnais effect was successfull"));

      if($amount == 1) {
        bang::$instance->gamestate->nextState("finishedReaction");
        return;
      } else {
        BangNotificationManager::tell(clienttranslate('But ${player_name} needs another miss'), ['player_name' => $this->getName()]); // TODO : are you sure this is the right name ?
        $amount--;
        $args = BangCardManager::getCurrentCard()->getReactionOptions($this);
        $args['missedNeeded'] = $amount;
        BangLog::addCardPlayed(BangPlayerManager::getCurrentTurn(true), BangCardManager::getCurrentCard(), $args);
      }
    } else {
      // Failure
      BangNotificationManager::tell(clienttranslate("Jourdonnais effect failed"));
    }

    $args = $this->getDefensiveOptions();
    BangNotificationManager::updateOptions($this, $args);
  }
}
