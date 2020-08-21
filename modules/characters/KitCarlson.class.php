<?php

class KitCarlson extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = KIT_CARLSON;
    $this->character_name = clienttranslate('Kit Carlson');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    if(Utils::getStateName() == 'drawCards') {
      $id = $this->id;
      BangCardManager::createSelection(3, $id);
      BangLog::addAction("selection", ['players' => [$id, $id], 'src' => $this->character_name]);
      return 'selection';
    } else {
      return parent::drawCards($amount);
    }
  }

  public function useAbility($args) {
    foreach ($args['selected'] as $card)
      BangCardManager::moveCard($card, 'hand', $this->id);
    BangCardManager::putOnDeck($args['rest'][0]);
    BangNotificationManager::gainedCards($this, BangCardManager::toObjects($args['selected']));
    // todo notification
    return "play";
  }

}
