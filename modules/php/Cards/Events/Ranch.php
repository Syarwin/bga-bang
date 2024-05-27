<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class Ranch extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RANCH;
    $this->name = clienttranslate('Ranch');
    $this->text = clienttranslate('At the end of his phase 1, each player may discard any number of cards from his hand to draw the same number of cards from the deck.');
    $this->effect = EFFECT_ENDOFPHASEONE;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $player
   */
  public function resolveEffect($player = null)
  {
    $atom = Stack::newAtom(ST_RANCH, [
      'pId' => $player->getId(),
    ]);
    Stack::insertOnTop($atom);
  }
}
