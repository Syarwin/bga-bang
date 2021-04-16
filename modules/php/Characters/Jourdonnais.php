<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use bang;

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

  // TODO: Use one source of truth for used Barrel and this skill. And it should NOT be a DB or probably not global. Stack?
  protected function canUseAbility()
  {
    return bang::get()->getGameStateValue('JourdonnaisUsedSkill') == 0;
  }

  protected function logUseAbility()
  {
    bang::get()->setGameStateValue('JourdonnaisUsedSkill', 1);
  }

  public function getDefensiveOptions()
  {
    $res = parent::getDefensiveOptions();
    if ($this->canUseAbility() && Stack::top()['src']['type'] == CARD_BANG) {
      $res['character'] = JOURDONNAIS;
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
    $this->logUseAbility();
    Notifications::flipCard($this, $card, $this);
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;
    Stack::shift();
    if ($card->getCopyColor() == 'H') {
      // Success
      Notifications::tell(clienttranslate('Jourdonnais effect was successful'));
      if ($missedNeeded == 1) {
        Stack::nextState();
        return;
      }
      // Slab the Killer
      Notifications::tell(clienttranslate('But ${player_name} needs another miss'), [
        'player_name' => $this->getName(),
      ]);
      $atom = Stack::top();
      $atom['missedNeeded'] = $missedNeeded - 1;
      Stack::insertAfter($atom);
      Stack::nextState();
    } else {
      Notifications::tell(clienttranslate('Jourdonnais effect failed'));
      Stack::resolve();
    }
  }
}
