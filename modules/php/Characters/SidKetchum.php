<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\Player;

class SidKetchum extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = SID_KETCHUM;
    $this->character_name = clienttranslate('Sid Ketchum');
    $this->text = [clienttranslate('He may discard 2 cards to regain 1 life point')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  protected function addAbility(array $options): array
  {
    if ($this->countHand() > 1 && Rules::isAbilityAvailable()) {
      $options['character'] = SID_KETCHUM;
    }
    return $options;
  }

  public function getHandOptions(): array
  {
    return $this->addAbility(parent::getHandOptions());
  }

  public function getBeerOptions(): array
  {
    return $this->addAbility(parent::getBeerOptions());
  }

  public function useAbility(array $args): void
  {
    Notifications::tell(
      clienttranslate('${player_name} uses the ability of Sid Ketchum by discarding 2 cards to regain 1 life point'),
      ['player_name' => $this->name]
    );

    Cards::discardMany($args);
    if (Globals::getIsMustPlayCard() && in_array(Globals::getMustPlayCardId(), $args)) {
      Globals::setIsMustPlayCard(false);
      Globals::setMustPlayCardId(0);
    }
    Notifications::discardedCards($this, $args);
    $this->gainLife();
    $this->addRevivalAtomOrEliminate();
  }
}
