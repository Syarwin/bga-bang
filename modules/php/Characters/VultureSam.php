<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\Player;

class VultureSam extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = VULTURE_SAM;
    $this->character_name = clienttranslate('Vulture Sam');
    $this->text = [
      clienttranslate('Whenever a character is eliminated from the play, he takes all the cards of that player.'),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function onPlayerPreEliminated(Player $player): void
  {
    if (Rules::isAbilityAvailable()) {
      // TODO send a single notification?
      foreach ($player->getHand() as $card) {
        Cards::move($card->getId(), LOCATION_HAND, $this->id);
        Notifications::stoleCard($this, $player, $card, false);
      }
      foreach ($player->getCardsInPlay() as $card) {
        Cards::move($card->getId(), LOCATION_HAND, $this->id);
        Notifications::stoleCard($this, $player, $card, true);
      }
    }
  }
}
