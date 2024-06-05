<?php
namespace BANG\Cards\Events;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Models\AbstractEventCard;

class RussianRoulette extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RUSSIAN_ROULETTE;
    $this->name = clienttranslate('Russian Roulette');
    $this->text = clienttranslate('When Russian Roulette enters play, starting from the Sheriff each player discards a Missed!, until one player does not: he loses 2 life points and the Roulette stops.');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function resolveEffect($player = null)
  {
    $players = Players::getLivingPlayersStartingWith($player);

    foreach (array_reverse($players->toArray()) as $player) {
      $atom = Stack::newSimpleAtom(ST_RUSSIAN_ROULETTE, $player->getId());
      Stack::insertOnTop($atom);
    }
  }
}
