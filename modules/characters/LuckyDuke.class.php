<?php

class LuckyDuke extends BangPlayer {
  private $selectedCard = null;
  public function __construct($row = null)
  {
    $this->character    = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text  = [
      clienttranslate("Each time he is required to “draw!,�? he flips the top two cards from the deck, and chooses the result he prefers."),
      clienttranslate("Discard both cards afterwards.")
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function draw($args, $src) {
    if(isset($args['pattern'])) {
      $cards = [BangCardManager::draw(), BangCardManager::draw()];
      if(preg_match($args['pattern'],$cards[0]->getCopy()))
        return $cards[0];
      return $cards[1];
    }
    if(!is_null($this->$selectedCard)) return BangCardManager::getCard($this->$selectedCard());
    BangCardManager::createSelection(2);
    BangLog::addAction("selection", ["players" => [$this->id], 'src' => $this->character_name]);
    return "select";
  }

  public function useAbility($args) {
    $this->$selectedCard = $args[0];
    return BangCardManager::getCurrentCard()->play($this, []);
  }
}
