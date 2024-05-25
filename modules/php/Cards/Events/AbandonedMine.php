<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class AbandonedMine extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_ABANDONED_MINE;
    $this->name = clienttranslate('Abandoned Mine');
    $this->text = clienttranslate('During their phase 1, each player draws from the discards (if they run out, from the deck). In their phase 3 they discard face down on the deck.');
    $this->effect = EFFECT_PERMANENT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param string $requestedLocation
   * @return string
   */
  public function getDrawCardsLocation($requestedLocation)
  {
    switch ($requestedLocation) {
      case LOCATION_DECK:
        return LOCATION_DISCARD;
      case LOCATION_DISCARD:
        return LOCATION_DECK;
      default:
        throw new \BgaVisibleSystemException('Unexpected location to draw from: ' . $requestedLocation);
    }
  }
}
