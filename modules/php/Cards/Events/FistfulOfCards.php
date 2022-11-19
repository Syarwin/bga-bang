<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class FistfulOfCards extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_FISTFUL_OF_CARDS;
    $this->name = clienttranslate('A Fistful Of Cards');
    $this->text = clienttranslate('At the beginning of his turn, each player is the target of as many "Bang!" as the number of cards in his hand. This must be always the last card, and stays in play until the game ends.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->lastCard = true;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
