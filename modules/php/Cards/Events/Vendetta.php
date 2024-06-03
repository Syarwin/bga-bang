<?php
namespace BANG\Cards\Events;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class Vendetta extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_VENDETTA;
    $this->name = clienttranslate('Vendetta');
    $this->text = clienttranslate('Players "Draw!" at the end of their turn; on a Heart, they play an additional turn (but does not "Draw!" again).');
    $this->effect = EFFECT_NEXTPLAYER;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $currentPlayer
   * @return int
   */
  public function getNextPlayerId($currentPlayer)
  {
    if (Globals::getVendettaWasUsed()) {
      Globals::setVendettaWasUsed(false);
      return parent::getNextPlayerId($currentPlayer);
    } else {
      $flipped = Cards::drawForLocation(LOCATION_FLIPPED, 1)->first();
      $src = EventCards::getActive();
      $player = Players::getCurrent();
      Notifications::flipCard($player, $flipped, $src);
      Cards::discard($flipped);
      if ($flipped->getSuit() === 'H') {
        Globals::setVendettaWasUsed(true);
        return $currentPlayer->getId();
      } else {
        return parent::getNextPlayerId($currentPlayer);
      }
    }
  }
}
