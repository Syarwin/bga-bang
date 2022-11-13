<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Helpers\Sounds;
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
    if (is_null(Cards::getLastDiscarded())) {
      parent::drawCardsPhaseOne();
    } else {
      Stack::insertOnTop(Stack::newAtom(ST_ACTIVE_DRAW_CARD, [
        'pId' => $this->id,
      ]));
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
      $this->drawCards(2);
    } else {
      // Draw the first one from discard
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);
      // The second one from deck
      $this->drawCards(1);
      Notifications::playSound(Sounds::getSoundForCharacterAbility());
    }
  }
}
