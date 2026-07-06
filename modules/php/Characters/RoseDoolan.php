<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Managers\Rules;
use BANG\Models\Player;

class RoseDoolan extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = ROSE_DOOLAN;
    $this->character_name = clienttranslate('Rose Doolan');
    $this->text = [clienttranslate('She sees all players at distance -1')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function isInRange(Player $enemy, int $range): bool
  {
    if (Rules::isAbilityAvailable()) {
      return parent::isInRange($enemy, $range + 1);
    } else {
      return parent::isInRange($enemy, $range);
    }
  }

  public function getDistances(): array
  {
    $dist = parent::getDistances();
    if (Rules::isAbilityAvailable()) {
      foreach ($dist as $pId => &$d) {
        $d--;
      }
    }
    return $dist;
  }
}
