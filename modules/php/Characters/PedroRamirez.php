<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

class PedroRamirez extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text = [
      clienttranslate(
        'During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsAbility()
  {
    if (is_null(Cards::getLastDiscarded())) {
      Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_END => 2]);
    } else {
      Stack::insertOnTop(Stack::newSimpleAtom(ST_ACTIVE_DRAW_CARD, $this));
    }
  }

  public function argDrawCard()
  {
    $options = [LOCATION_DECK, LOCATION_DISCARD];
    return ['options' => $options];
  }

  public function useAbility($args)
  {
    if ($args['selected'] == LOCATION_DECK) {
      $cardsToDraw = 2;
    } else {
      // Draw the first one from discard
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);
      // The second one from deck
      $cardsToDraw = 1;
    }
    Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_END => $cardsToDraw]);
  }
}
