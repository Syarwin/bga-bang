<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Game\Utils;
use Bang\Cards\Cards;

class BlackJack extends Player {
  public function __construct($row = null)
  {
    $this->character    = BLACK_JACK;
    $this->character_name = clienttranslate('Black Jack');
    $this->text  = [
      clienttranslate("during phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCards($amount) {
    // TODO : maybe going to another state at startOfTurn would be safer to detect phase 1 ?
    // Power only applies at phase 1
    if(Utils::getStateName() != 'drawCards')
      return parent::drawCards($amount);

    // Draw one card not visible
    $cards = Cards::deal($this->id, 1);
    Notifications::drawCards($this, $cards);
    // Then draw one visible
    $cards = Cards::deal($this->id, 1);
    Notifications::drawCards($this, $cards, true);

    // If heart or diamond => draw again a private one
    $card = $cards[0];
    if(in_array($card->getCopyColor(), ['H', 'D'])) {
      $cards = Cards::deal($this->id, 1);
      Notifications::drawCards($this, $cards);
    }
  }
}
