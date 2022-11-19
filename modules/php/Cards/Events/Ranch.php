<?php
namespace BANG\Cards\Events;
use BANG\Models\AbstractEventCard;

class Ranch extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RANCH;
    $this->name = clienttranslate('Ranch');
    $this->text = clienttranslate('Once at the end of his turn, each player may discard any number of cards from his hand to draw an equal number of cards from the deck.');
    $this->effect = EFFECT_ENDOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }
}
