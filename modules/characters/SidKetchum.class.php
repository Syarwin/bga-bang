<?php

class SidKetchum extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = SID_KETCHUM;
    $this->character_name = clienttranslate('Sid Ketchum');
    $this->text  = [
      clienttranslate("He may discard 2 cards to regain 1 life point"),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getHandOptions() {
    $res = parent::getHandOptions();
    $res['character'] = SID_KETCHUM;
    return $res;
  }

  public function useAbility($args) {
    BangNotificationManager::tell('${player_name} uses his ability', ['player_name' => $this->name]);
    foreach ($args as $card) BangCardManager::playCard($card);
    BangNotificationManager::discardedCards($this, array_map(['BangCardManager','getCard'], $args));
    $this->gainLife();
  }
}
