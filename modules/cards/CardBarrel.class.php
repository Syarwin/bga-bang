<?php

class CardBarrel extends BangBlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_BARREL;
    $this->name  = clienttranslate('Barrel');
    $this->text  = clienttranslate("Reveal top card from the deck when you're attacked. If it's a heart it's a miss.");
    $this->symbols = [
      [SYMBOL_DRAW_HEART, SYMBOL_MISSED]
    ];
    $this->copies = [
      BASE_GAME => [ 'QS', 'KS' ],
      DODGE_CITY => [ ],
    ];
    $this->effect = ['type' => DEFENSIVE ];
  }

  public function activate($player, $args = []) {
    BangNotificationManager::tell('${player_name } uses ${card_name}', ['player_name'=>$player->getName(), 'card_name' => $this->name]);
    $mixed = $player->draw(['pattern' => "/H/"], $this);
    if(!$mixed instanceof BangCard)
      return $mixed; //shouldn't happen, just in case we decide to let player decide after all

    BangCardManager::markAsPlayed($this->id);
    $args = BangCardManager::getCurrentCard()->getReactionOptions($player);
    if ($mixed->getCopyColor() == 'H') {
      BangNotificationManager::tell('Barrel was successfull');
      if($args['amount'] == 1)
            return null;
      BangNotificationManager::tell('But ${player_name} needs another miss', ['player_name' => $player->getName()]);
      $args['amount']--;
      BangLog::addCardPlayed(BangPlayerManager::getCurrentTurn(true), BangCardManager::getCurrentCard(), $args);

    } else {
      BangNotificationManager::tell('Barrel failed');
    }
    return "updateOptions";
  }
}
