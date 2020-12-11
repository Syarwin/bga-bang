<?php
namespace Bang\Cards;
use Bang\Cards\Card;
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


//  wasPlayed
  public function activate($player, $args = []) {
    Notifications::useCard($player, $this);

    $mixed = $player->flip(['pattern' => "/H/"], $this);
    if(!$mixed instanceof Card)
      return $mixed; //shouldn't happen, just in case we decide to let player decide after all


    Cards::markAsPlayed($this->id);
    $args = Log::getLastAction('cardPlayed');
    $args['missedNeeded'] = $args['missedNeeded'] ?? 1;

    // Draw an heart => success
    if ($mixed->getCopyColor() == 'H') {
      Notifications::tell(clienttranslate('Barrel was successfull'));
      if($args['missedNeeded'] == 1)
        return null;

      // Against Slab the Killer, need to miss => update args
      Notifications::tell(clienttranslate('But ${player_name} needs another miss'), ['player_name' => $player->getName()]);
      $args['missedNeeded']--;
    } else {
      Notifications::tell(clienttranslate('Barrel failed'));
    }

    Log::addCardPlayed(Players::getCurrentTurn(true), Cards::getCurrentCard(), $args);
    return "updateOptions";
  }
}
