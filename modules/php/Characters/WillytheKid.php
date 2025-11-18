<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Managers\Rules;
use BANG\Models\Player;

class WillytheKid extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = WILLY_THE_KID;
    $this->character_name = clienttranslate('Willy the Kid');
    $this->text = [clienttranslate('He can play any number of BANG! during his turn. ')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function hasUnlimitedBangs(): bool
  {
    return Rules::isAbilityAvailable() || parent::hasUnlimitedBangs();
  }
}
