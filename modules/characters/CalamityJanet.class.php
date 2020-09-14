<?php

class CalamityJanet extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text  = [
      clienttranslate("He can play BANG! cards as Missed! cards and vice versa. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getBangCards() {
    $res = parent::getBangCards();
    $hand = BangCardManager::getHand($this->id);
    foreach($hand as $card) {
      if($card->getType() == CARD_MISSED)
        $res['cards'][] = ['id' => $card->getID(), 'options' => ['type' => OPTION_NONE]];
    }
    return $res;
  }

  public function getDefensiveOptions() {
    return array_merge_recursive(parent::getDefensiveOptions(), parent::getBangCards());
  }

  public function getHandOptions() {
    $res = parent::getHandOptions();
    $hand = BangCardManager::getHand($this->id);
    $bang = new CardBang();
    $options = $bang->getPlayOptions($this);
    foreach($hand as $card) {
      if($card->getType() == CARD_MISSED)
        $res['cards'][] = ['id' => $card->getID(), 'options' => $options];
    }
    return $res;
  }

  public function playCard($id, $args) {
    $card = BangCardManager::getCard($id);
    if($card->getType() == CARD_MISSED) {
      BangNotificationManager::cardPlayed($this, $card, $args);
      BangLog::addCardPlayed($this, $card, $args);
      $card = new CardBang($id, "");
      $newstate = $card->play($this, $args);
      return $newstate;
    }
    return parent::playCard($id, $args);
  }

}
