<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class BloodBrothers extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_BLOOD_BROTHERS;
    $this->name = clienttranslate('Blood Brothers');
    $this->text = clienttranslate('Each player may choose to lose one of his life points to give to another player at the beginning of his turn.');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player|null $player
   * @return void
   */
  public function resolveEffect($player = null)
  {
    $players = Players::getLivingPlayersStartingWith($player);

    foreach (array_reverse($players->toArray()) as $player) {
      $atom = Stack::newSimpleAtom(ST_BLOOD_BROTHERS, $player->getId());
      Stack::insertOnTop($atom);
    }
  }
}
