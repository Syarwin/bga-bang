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
      HIGH_NOON => [],
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
    Stack::suspendCtx();
    $player->addFlipAtom($this);
  }

  public function resolveFlipped($card, $player)
  {
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;

    // Draw an heart => success
    $event = null;
    if ($card->getCopyColor($event) == 'H') {
      Notifications::tell(clienttranslate('Barrel was successful ${flipEventMsg}'), ['event' => $event]);
      $missedNeeded -= 1;
    } else {
      Notifications::tell(clienttranslate('Barrel failed ${flipEventMsg}'), [
        'event' => ($card->getSuit() !== 'H') ? null : $event //result changed because of event?
      ]);
    }
    Stack::updateAttackAtomAfterAction($missedNeeded, $this->type);
  }
}
