<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

class BlackJack extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = BLACK_JACK;
    $this->character_name = clienttranslate('Black Jack');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsAbility()
  {
    // Draw one visible
    $cards = Cards::deal($this->id, 1);
    Notifications::drawCards($this, $cards, true);

    // If heart or diamond => draw again a private one
    $card = $cards->first();
    if (in_array($card->getCopyColor(), ['H', 'D'])) {
      Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_END => 1]);
    }
    $this->onChangeHand();
  }
}
