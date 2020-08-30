<?php

class JesseJones extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = JESSE_JONES;
    $this->character_name = clienttranslate('Jesse Jones');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    if(Utils::getStateName() == 'drawCards') {
      $options = BangPlayerManager::getLivingPlayers($this->id);
      Utils::filter($options, function($id) {
        $hand = BangPlayerManager::getPlayer($id)->getCardsInHand();
        return !empty($hand);
      });
      $options[] = 'deck';
      BangLog::addAction("draw", $options);
      return 'draw';
    } else {
      return parent::drawCards($amount);
    }
  }

  public function useAbility($args) {
    if($args['selected'] == 'deck') {
      $cards = BangCardManager::deal($this->id, 2);
      BangNotificationManager::drawCards($this, $cards);
    } else {
      // Stole the first card
      $victim = BangPlayerManager::getPlayer($args['selected']);
      $card= $victim->getRandomCardInHand();
      BangCardManager::moveCard($card, 'hand', $this->id);
      BangNotificationManager::stoleCard($this, $victim, $card, false);
      // Deal the second one
      $card = BangCardManager::deal($this->id, 1);
      BangNotificationManager::drawCards($this, $card);
    }
    return "play";
  }
}
