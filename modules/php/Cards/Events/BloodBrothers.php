<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class BloodBrothers extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_BLOOD_BROTHERS;
    $this->name = clienttranslate('Blood Brothers');
    $this->text = clienttranslate('At the beginning of his turn, each player may lose one life point (except the last one) to give one life point to any player of his choice.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player|null $player
   * @return void
   */
  public function resolveEffect($player = null)
  {
    $atom = Stack::newSimpleAtom(ST_BLOOD_BROTHERS, $player->getId());
    Stack::insertOnTop($atom);
  }
}
