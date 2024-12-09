<?php
namespace BANG\Cards\Events;
use BANG\Core\Globals;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class Vendetta extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_VENDETTA;
    $this->name = clienttranslate('Vendetta');
    $this->text = clienttranslate('At the end of his turn, each player "draws!": on a Heart, he plays another turn (but he does not "draw!" again).');
    $this->effect = EFFECT_END_OF_TURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $player
   * @return void
   */
  public function resolveEffect($player = null)
  {
    if (Globals::getVendettaWasUsed()) {
      Globals::setVendettaWasUsed(false);
    } else {
      $player->addFlipAtom($this);
      Globals::setVendettaWasUsed(true);
    }
  }

  public function resolveFlipped($card)
  {
    if ($card->getSuit() !== 'H') {
      Globals::setVendettaWasUsed(false);
    }
  }
}
