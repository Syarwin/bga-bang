<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class HardLiquor extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_HARD_LIQUOR;
    $this->name = clienttranslate('Hard Liquor');
    $this->text = clienttranslate('Each player may skip his drawing phase 1 to regain 1 life point.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $player
   */
  public function resolveEffect($player = null)
  {
      $atom = Stack::newAtom(ST_HARD_LIQUOR, [
        'pId' => $player->getId(),
      ]);
      Stack::insertOnTop($atom);
  }

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 0;
  }
}
