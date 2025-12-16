<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\Player;

class LuckyDuke extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = LUCKY_DUKE;
    $this->character_name = clienttranslate('Lucky Duke');
    $this->text = [
      clienttranslate(
        'Each time he is required to "draw!", he flips the top two cards from the deck, and chooses the result he prefers.'
      ),
      clienttranslate('Discard both cards afterwards.'),
    ];
    $this->bullets = 4;
    parent::__construct($row);

    $this->selectedCard = null;
  }

  public function flip($src): void
  {
    if (Rules::isAbilityAvailable()) {
      $cards = Cards::drawForLocation(LOCATION_SELECTION, 2);
      foreach ($cards as $card) {
        Notifications::flipCard($this, $card, $src);
      }

      Log::addAction('selection', ['players' => [$this->id], 'src' => $src->getName()]);
      parent::addResolveFlippedAtom($src);
      $this->prepareSelection($src, [$this->getId()], true, 1, true);
    } else {
      parent::flip($src);
    }
  }
}
