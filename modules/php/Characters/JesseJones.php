<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Game\Utils;
use Bang\Cards\Cards;

class JesseJones extends Player {
  public function __construct($row = null)
  {
    $this->character    = JESSE_JONES;
    $this->character_name = clienttranslate('Jesse Jones');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function statePhaseOne() {
    $options = Players::getLivingPlayers($this->id);
    Utils::filter($options, function($id) {
      $hand = Players::getPlayer($id)->getCardsInHand();
      return !empty($hand);
    });
    $options[] = 'deck';
    Log::addAction("draw", $options);
    return 'activeDraw';
  }


  public function useAbility($args) {
    if($args['selected'] == 'deck') {
      $cards = Cards::deal($this->id, 2);
      Notifications::drawCards($this, $cards);
    } else {
      // Stole the first card
      $victim = Players::getPlayer($args['selected']);
      $card= $victim->getRandomCardInHand();
      Cards::moveCard($card, 'hand', $this->id);
      Notifications::stoleCard($this, $victim, $card, false);
      // Deal the second one
      $card = Cards::deal($this->id, 1);
      Notifications::drawCards($this, $card);
    }
    return "play";
  }
}
