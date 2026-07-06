<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\AbstractCard;
use BANG\Models\Player;

class Jourdonnais extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = JOURDONNAIS;
    $this->character_name = clienttranslate('Jourdonnais');
    $this->text = [clienttranslate('Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  protected function abilityHaveBeenUsed(): bool
  {
    $atom = Stack::top();
    return isset($atom['used']) && in_array($this->character, $atom['used']);
  }

  public function getDefensiveOptions(): array
  {
    $res = parent::getDefensiveOptions();

    if (!$this->abilityHaveBeenUsed() && Rules::isAbilityAvailable()) {
      $res['character'] = $this->character;
    }
    return $res;
  }

  public function useAbility(): void
  {
    Cards::drawForLocation(LOCATION_FLIPPED, 1);
    Stack::suspendCtx();
    $this->addResolveFlippedAtom($this);
  }

  public function resolveFlipped(AbstractCard $card): void
  {
    Notifications::flipCard($this, $card, $this);
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;

    $suitOverrideInfo = Rules::getSuitOverrideInfo($card, 'H');
    if ($suitOverrideInfo['flipSuccessful']) {
      Notifications::tell(clienttranslate('Jourdonnais effect was successful${flipEventMsg}'), $suitOverrideInfo);
      $missedNeeded -= 1;
    } else {
      Notifications::tell(clienttranslate('Jourdonnais effect failed${flipEventMsg}'), $suitOverrideInfo);
    }

    Stack::updateAttackAtomAfterAction($missedNeeded, $this->character);
    $this->notifyAboutAnotherMissed();
  }
}
