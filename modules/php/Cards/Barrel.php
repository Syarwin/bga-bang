<?php
namespace BANG\Cards;
use BANG\Core\Stack;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;

class Barrel extends \BANG\Models\BlueCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_BARREL;
    $this->name = clienttranslate('Barrel');
    $this->text = clienttranslate("Reveal top card from the deck when you're attacked. If it's a heart it's a miss.");
    $this->symbols = [[SYMBOL_DRAW_HEART, SYMBOL_MISSED]];
    $this->copies = [
      BASE_GAME => ['QS', 'KS'],
      DODGE_CITY => [],
    ];
    $this->effect = ['type' => DEFENSIVE];
  }

  public function wasPlayed()
  {
    $atom = Stack::top();
    return isset($atom['used']) && in_array($this->type, $atom['used']);
  }

  public function activate($player, $args = [])
  {
    Notifications::useCard($player, $this);
    $player->addFlipAtom($this);
    Stack::resolve();
  }

  public function resolveFlipped($card)
  {
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;
    Stack::shift();

    // Draw an heart => success
    if ($card->getCopyColor() == 'H') {
      Notifications::tell(clienttranslate('Barrel was successful'));

      $missedNeeded -= 1;
    } else {
      Notifications::tell(clienttranslate('Barrel failed'));
    }
    $newAtom = Utils::updateAtomAfterAction(Stack::top(), $missedNeeded, $this->type);
    Stack::insertAfter($newAtom);
  }
}
