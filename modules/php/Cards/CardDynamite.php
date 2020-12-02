<?php
namespace Bang\Cards;
use Bang\Game\Notifications;
use Bang\Characters\Players;

class CardDynamite extends BlueCard {
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
   // TODO : strings not translatable
  public function activate($player, $args=[]) {
    Log::addCardPlayed($player, $this,[]);
    $mixed = $player->draw($args, $this);
    if($mixed instanceof Bang\Cards\Card) {
      $val = $mixed->getCopyValue();
      if ($mixed->getCopyColor() == 'S' && is_numeric($val) && intval($val) < 10) {
        Notifications::tell("Dynamite explodes");
        Cards::discardCard($this->id);
        Notifications::discardedCard($player, $this, true);

        // Loose 3hp: if the player dies, skip its turn
        $newstate = $player->looseLife(3);
        return is_null($newstate) ? "draw" : $newstate;
      } else {
        // Move to next player and go on
        $next = Players::getNextPlayer($player);
        Cards::moveCard($this->id, 'inPlay', $next->getId());
        Notifications::moveCard($this, $player, $next);
        return "draw";
      }
    }
    return $mixed;
  }
}
