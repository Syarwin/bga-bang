<?php

class CardDynamite extends BangCard {
  public function __construct($id=null)
  {
    parent::__construct($id);
    $this->type  = CARD_DYNAMITE;
    $this->name  = clienttranslate('Dynamite');
    $this->text  = clienttranslate("At the start of your turn reveal top card from the deck. If it's Spades 2-9, you lose 3 life points. Else pass the Dynamite to the player on your left.");
    $this->color = BLUE;
    $this->effect = ['type' => STARTOFTURN];
    $this->symbols = [
      [SYMBOL_DYNAMITE, clienttranslate("Lose 3 life points. Else pass the Dynamite on your left.")]
    ];
    $this->copies = [
      BASE_GAME => [ '2H' ],
      DODGE_CITY => [ ],
    ];
  }

  public function activate($player, $args=[]) {
    $card = $player->draw($args, $this);
    if(is_null($card)) return;
    $val = $card->format()['value'];
    if ($card->format()['color'] == 'S' && is_numeric($val) && intval($val)<10) {
      BangNotificationManager::tell("Dynamite explodes");
      BangCardManager::playCard($this->id);
      BangNotificationManager::discardedCard($player, $this, true);
      for($i = 0; $i < 3; $i++) {
        if($player->looseLife()) {
          return "skip";
        }
      }
      return null;
    } else {
      $next = BangPlayerManager::getNextPlayer($player->getId());
      BangCardManager::moveCard($this->id, 'inPlay', $next->getId());
      BangNotificationManager::moveCard($this, $player, $next);
      return null;
    }
  }
}
