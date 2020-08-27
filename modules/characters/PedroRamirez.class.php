<?php

class PedroRamirez extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text  = [
      clienttranslate("During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    if(Utils::getStateName() == 'drawCards') {
      BangLog::addAction("draw", ['deck', 'discard']);
      return 'draw';
    } else {
      return parent::drawCards($amount);
    }
  }

  public function useAbility($args) {
    $cards = [];
    if($args['selected'] == 'deck') {
      $cards = BangCardManager::deal($this->id, 2);
    } else {
      $cards[] = BangCardManager::deal($this->id, 1, true);
      $cards[] = BangCardManager::deal($this->id, 1);
      BangNotificationManager::tell("${player_name} chooses ${card_name} picks from the discard pile", ['player_name' => $this->name, 'card_name' => $cards[0]->getName()]);
    }
    BangNotificationManager::gainedCards($this, $cards);
    return "play";
  }

}
