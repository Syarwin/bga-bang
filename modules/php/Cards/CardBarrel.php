<?php
namespace Bang\Cards;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Characters\Players;

class CardBarrel extends BlueCard {
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

  // TODO : strings not translated
  public function activate($player, $args = []) {
    Notifications::tell('${player_name } uses ${card_name}', ['player_name'=>$player->getName(), 'card_name' => $this->name]);
    $mixed = $player->draw(['pattern' => "/H/"], $this);
    if(!$mixed instanceof Bang\Cards\Card)
      return $mixed; //shouldn't happen, just in case we decide to let player decide after all
    Cards::markAsPlayed($this->id);
    $args = Log::getLastAction('cardPlayed');
    if(!isset($args['missedNeeded'])) $args['missedNeeded'] = 1;
    if ($mixed->getCopyColor() == 'H') {
      Notifications::tell('Barrel was successfull');
      if($args['missedNeeded'] == 1)
            return null;
      Notifications::tell('But ${player_name} needs another miss', ['player_name' => $player->getName()]);
      $args['missedNeeded']--;
      Log::addCardPlayed(Players::getCurrentTurn(true), Cards::getCurrentCard(), $args);

    } else {
      Notifications::tell('Barrel failed');
    }
    return "updateOptions";
  }
}
