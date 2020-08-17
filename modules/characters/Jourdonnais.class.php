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
    $card = BangCardManager::getCard(bang::$instance->getGameStateValue('currentCard'));
    if($card->getType() == CARD_BANG && bang::$instance->getGameStateValue('JourdonnaisUsedSkill')==0)
        $res['character'] = OPTION_NONE;
    return $res;
  }

  public function useAbility($args) {
    bang::$instance->setGameStateValue('JourdonnaisUsedSkill', 1);
    $card = $player->draw([], $this);
    BangCardManager::markAsPlayed($this->id);
    if ($card->format()['color'] == 'H') {
      BangNotificationManager::tell('Jourdonnais blocked the attack');
      bang::$instance->gamestate->nextState( "react" );
    } else {
      BangNotificationManager::tell('Jourdonnais failed to block the attack');
      $args = $this->getDefensiveOptions();
      BangNotificationManager::updateOptions($this, $args);
    }
  }
}
