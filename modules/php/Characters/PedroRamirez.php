<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;

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


  public function drawCardsPhaseOne()
  {
    // TODO : auto skip if discard is empty
    Stack::insertOnTop([
      'state' => ST_ACTIVE_DRAW_CARD,
      'pId' => $this->id,
    ]);
  }

  public function argDrawCard()
  {
    $options = [LOCATION_DECK];
    if (!is_null(Cards::getLastDiscarded())) {
      $options[] = LOCATION_DISCARD;
    }

    return ['options' => $options];
  }

  public function useAbility($args)
  {
    if ($args['selected'] == LOCATION_DECK) {
      $this->drawCards(2);
    } else {
      // Draw the first one from discard
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);
      // The second one from deck
      $this->drawCards(1);
    }
  }
}
