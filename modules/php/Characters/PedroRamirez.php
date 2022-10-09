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
      Rules::incrementPhaseOneDrawEndAmount();
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
      $cardsToDraw = 1;
    } else {
      // Draw the first one from discard
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);

      // Second one is already implied in Rules
      $cardsToDraw = 0;
    }
    Rules::incrementPhaseOneDrawEndAmount($cardsToDraw);
  }

  public function getPhaseOneRules($defaultAmount)
  {
    if (Rules::isAbilityAvailable()) {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 0,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => $defaultAmount - 1
      ];
    } else {
      return parent::getPhaseOneRules($defaultAmount);
    }
  }
}
