<?php

class CardDynamite extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_DYNAMITE;
    $this->name  = clienttranslate('Dynamite');
    $this->text  = clienttranslate("At the start of your turn reveal top card from the deck. If it's Spades 2-9, you lose 3 life points. Else pass the Dynamite to the player on your left.");
    $this->symbols = [
      [SYMBOL_DYNAMITE, clienttranslate("Lose 3 life points. Else pass the Dynamite on your left.")]
    ];
    $this->copies = [
      BASE_GAME => [ '2H' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => STARTOFTURN];
  }

  /*
   * When activated at the start of turn, draw a card and resolve effect
   */
  public function activate($player, $args=[]) {
    $mixed = $player->draw($args, $this);
    if($mixed instanceof BangCard) {
      $val = $mixed->getCopyValue();
      if ($mixed->getCopyColor() == 'S' && is_numeric($val) && intval($val) < 10) {
        BangNotificationManager::tell("Dynamite explodes");
        BangCardManager::discardCard($this->id);
        BangNotificationManager::discardedCard($player, $this, true);

        // Loose 3hp: if the player dies, skip its turn
        $newstate = $player->looseLife("dynamite", 3);
        if(is_null($newstate)) return $player->isEliminated() ? "skip" : "draw";
        return $newstate;
      } else {
        // Move to next player and go on
        $next = BangPlayerManager::getNextPlayer($player->getId());
        BangCardManager::moveCard($this->id, 'inPlay', $next);
        BangNotificationManager::moveCard($this, $player, $next);
        return null;
      }
    }
    return $mixed;
  }
}
