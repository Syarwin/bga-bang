<?php
namespace Bang\Cards;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Characters\Players;
use Bang\Cards\Card;

class CardDynamite extends BlueCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_DYNAMITE;
    $this->name  = clienttranslate('Dynamite');
    $this->text  = clienttranslate("At the start of your turn reveal top card from the deck. If it's Spades 2-9, you loose 3 life points. Else pass the Dynamite to the player on your left.");
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
   * When activated at the start of turn, flip a card and resolve effect
   */
  public function activate($player, $args = []) {
    Log::addCardPlayed($player, $this, []);
    $mixed = $player->flip($args, $this);

    if($mixed instanceof Card) {
      // Beween 2 & 10 of spades ? => kaboom
      $val = $mixed->getCopyValue();
      if ($mixed->getCopyColor() == 'S' && is_numeric($val) && intval($val) < 10) {
        Notifications::tell(clienttranslate("Dynamite explodes"));
        Cards::discardCard($this->id);
        Notifications::discardedCard($player, $this, true);

        // Loose 3hp: if the player dies, skip its turn
        $newstate = $player->looseLife(3);
        return $newstate;
      }
      // Move to next player and go on
      else {
        // TODO : move to next player WITHOUT a dynamite...
        $next = Players::getNextPlayer($player);
        Cards::moveCard($this->id, 'inPlay', $next->getId());
        Notifications::moveCard($this, $player, $next);
        return 'draw'; // Required to work with Lucky Duke
      }
    }

    return $mixed;
  }
}
