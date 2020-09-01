<?php

class LuckyDuke extends BangPlayer {
  private $selectedCard = null;
  public function __construct($row = null)
  {
    $this->character    = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text  = [
      clienttranslate('Each time he is required to "draw!", he flips the top two cards from the deck, and chooses the result he prefers.'),
      clienttranslate("Discard both cards afterwards.")
    ];
    $this->bullets = 4;
    parent::__construct($row);

    $this->selectedCard = null;
  }

  public function draw($args, $src) {
    if(isset($args['pattern'])) {
      $cards = [BangCardManager::draw(), BangCardManager::draw()];
      BangNotificationManager::drawCard($this, $cards[0], $src);
      BangNotificationManager::drawCard($this, $cards[1], $src);
      if(preg_match($args['pattern'],$cards[0]->getCopy()))
        return $cards[0];
      return $cards[1];
    }
    if(!is_null($this->selectedCard)) return $this->selectedCard;
    $cards = BangCardManager::toObjects(BangCardManager::createSelection(2));
    BangNotificationManager::drawCard($this, $cards[0], $src);
    BangNotificationManager::drawCard($this, $cards[1], $src);
    BangLog::addAction("selection", ["players" => [$this->id], 'src' => $src->getName()]);
    return "select";
  }

  public function useAbility($args) {
    
    $this->selectedCard = BangCardManager::getCard($args['selected'][0]);

    $this->discardCard(BangCardManager::getCard($args['rest'][0]));
    BangNotificationManager::tell('${player_name} chooses ${card_name}', [
      'player_name' => $this->name,
      'card_name' => $this->selectedCard->getNameAndValue()
    ]);
    return BangCardManager::getCurrentCard()->activate($this, []);
  }
}
