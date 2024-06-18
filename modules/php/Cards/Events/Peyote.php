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
    $this->text = clienttranslate('Instead of drawing in his phase 1, each player guesses if the suit of the top card of the deck is red or black. He then draws and shows it: if he guessed right, he keeps it and may guess again; otherwise he proceeds to phase 2.');
    $this->effect = EFFECT_BEFORE_PHASE_ONE;
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

  public function isAllowPlayerPhaseOne()
  {
    return false;
  }
}
