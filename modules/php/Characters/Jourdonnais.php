<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

class Jourdonnais extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = JOURDONNAIS;
    $this->character_name = clienttranslate('Jourdonnais');
    $this->text = [clienttranslate('Whenever he is the target of a BANG!, he may draw!: on a Heart, he is missed.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  protected function abilityHaveBeenUsed()
  {
    $atom = Stack::top();
    return isset($atom['used']) && in_array($this->character, $atom['used']);
  }

  public function getDefensiveOptions()
  {
    $res = parent::getDefensiveOptions();

    if (!$this->abilityHaveBeenUsed() && Rules::isAbilityAvailable()) {
      $res['character'] = $this->character;
    }
    return $res;
  }

  public function useAbility()
  {
    Cards::drawForLocation(LOCATION_FLIPPED, 1);
    Stack::suspendCtx();
    $this->addResolveFlippedAtom($this);
  }

  public function resolveFlipped($card)
  {
    Notifications::flipCard($this, $card, $this);
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;

    $event = null;
    if ($card->getCopyColor($event) == 'H') {
      Notifications::tell(clienttranslate('Jourdonnais effect was successful${flipEventMsg}'), ['event' => $event]);
      $missedNeeded -= 1;
    } else {
      Notifications::tell(clienttranslate('Jourdonnais effect failed${flipEventMsg}'), [
        'event' => ($card->getSuit() !== 'H') ? null : $event //result changed because of event?
      ]);
    }

    Stack::updateAttackAtomAfterAction($missedNeeded, $this->character);
    $this->notifyAboutAnotherMissed();
  }
}
