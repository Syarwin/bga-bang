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
      Rules::incrementPhaseOneDrawEndAmount();
    }
    $this->onChangeHand();
  }

  public function getPhaseOneRules($defaultAmount)
  {
    if ($defaultAmount === 1) {
      return parent::getPhaseOneRules($defaultAmount);
    } else {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 1,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => $defaultAmount - 2
      ];
    }
  }
}
