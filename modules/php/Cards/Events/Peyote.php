<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class Peyote extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_PEYOTE;
    $this->name = clienttranslate('Peyote');
    $this->text = clienttranslate('Players try to guess the suit of the card they draw and keep drawing until they are wrong.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $player
   */
  public function resolveEffect($player = null)
  {
    $atom = Stack::newAtom(ST_PEYOTE, [
      'pId' => $player->getId(),
      'suspended' => true,
    ]);
    Stack::insertOnTop($atom);
  }

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 0;
  }
}
