<?php

declare(strict_types=1);

namespace BANG\Cards\Events;

use BANG\Managers\Players;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class Doctor extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_DOCTOR;
    $this->name = clienttranslate('The Doctor');
    $this->text = clienttranslate('When The Doctor enters in play, the player(s) still in the game with the fewest current life points regain(s) 1 life point');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = HIGH_NOON;
  }

  public function resolveEffect(Player $player): void
  {
    $players = Players::getLivingPlayers();
    $minHp = min($players->map(function ($player) { return $player->getHp(); })->toArray());
    $playersWithMinHp = $players->filter(function ($player) use ($minHp) { return $player->getHp() === $minHp; });
    foreach ($playersWithMinHp as $player) {
      $player->gainLife(1);
    }
  }
}
