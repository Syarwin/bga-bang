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

  public function getDefensiveOptions() {
    $res = parent::getDefensiveOptions();
    $card = BangCardManager::getCurrentCard();
    if($card->getType() == CARD_BANG && bang::$instance->getGameStateValue('JourdonnaisUsedSkill')==0)
        $res['character'] = OPTION_NONE;
    return $res;
  }

  public function useAbility($args) {
    bang::$instance->setGameStateValue('JourdonnaisUsedSkill', 1);
    $card = $player->draw([], $this);
    $args = BangCardManager::getCurrentCard()->getReactionOptions($player);
    if ($card->format()['color'] == 'H') {
      BangNotificationManager::tell('Jourdonnais effect was successfull');
      if($args['amount'] == 1) {
        bang::$instance->gamestate->nextState( "react" );
        return;
      }
      BangNotificationManager::tell('But ${player_name} needs another miss', ['player_name' => $player->getName()]);
      $args['amount']--;
      BangLog::addCardPlayed(BangPlayerManager::getCurrentTurn(true), BangCardManager::getCurrentCard(), $args);
    } else {
      BangNotificationManager::tell('Jourdonnais effect failed');
    }
    $args = $this->getDefensiveOptions();
    BangNotificationManager::updateOptions($this, $args);
  }
}
