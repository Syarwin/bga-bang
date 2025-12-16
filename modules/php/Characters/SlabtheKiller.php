<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Managers\Rules;
use BANG\Models\AbstractCard;
use BANG\Models\Player;

class SlabtheKiller extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = SLAB_THE_KILLER;
    $this->character_name = clienttranslate('Slab the Killer');
    $this->text = [clienttranslate('Players trying to cancel his BANG! cards need to play 2 Missed! ')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getReactAtomForAttack(AbstractCard $card, ?int $targetCardId = null, bool $secondMissedNeeded = false): array
  {
    $secondMissedNeeded = $secondMissedNeeded || ($card->getType() === CARD_BANG && Rules::isAbilityAvailable());
    return parent::getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded);
  }
}
