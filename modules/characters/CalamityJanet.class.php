<?php

class CalamityJanet extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text  = [
      clienttranslate("She can play BANG! cards as Missed! cards and vice versa. "),

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

}
