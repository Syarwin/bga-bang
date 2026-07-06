<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Managers\Rules;
use BANG\Models\Player;

class PaulRegret extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = PAUL_REGRET;
    $this->character_name = clienttranslate('Paul Regret');
    $this->text = [clienttranslate('All players see him at a distance +1')];
    $this->bullets = 3;
    parent::__construct($row);
  }

  public function getDistanceTo(Player $enemy): int
  {
    return parent::getDistanceTo($enemy) + (Rules::isAbilityAvailable() ? 1 : 0);
  }
}
