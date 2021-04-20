<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;

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
    // TODO: In expansion we need to set some property if card has a BANG! action. However it 100% corresponds with SYMBOL_BANG
    if (!$this->abilityHaveBeenUsed()) {
      $res['character'] = $this->character;
    }
    return $res;
  }

  public function useAbility()
  {
    Cards::drawForLocation(LOCATION_FLIPPED, 1);
    $this->addResolveFlippedAtom($this);
    Stack::resolve();
  }

  public function resolveFlipped($card)
  {
    Notifications::flipCard($this, $card, $this);
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;

    Stack::shift();
    if ($card->getCopyColor() == 'H') {
      Notifications::tell(clienttranslate('Jourdonnais effect was successful'));

      $missedNeeded -= 1;
    } else {
      Notifications::tell(clienttranslate('Jourdonnais effect failed'));
    }
    $newAtom = Utils::updateAtomAfterAction(Stack::top(), $missedNeeded, $this->character);
    Stack::insertAfter($newAtom);
    $this->handleMultipleMissed(true);
  }
}
