<?php
namespace BANG\Cards;
use BANG\Core\Globals;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Managers\Players;

class Barrel extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
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

  //  wasPlayed
  public function activate($player, $args = [])
  {
    Notifications::useCard($player, $this);
    $player->addFlipAtom($this);
    Stack::resolve();
  }

  public function resolveFlipped($card, $player)
  {
    Cards::markAsPlayed($this->id);
    $atom = Stack::top();
    $missedNeeded = $atom['missedNeeded'] ?? 1;

    // Draw an heart => success
    if ($card->getCopyColor() == 'H') {
      Notifications::tell(clienttranslate('Barrel was successful'));
      Stack::shift();
      if ($missedNeeded == 1) {
        return;
      }

      // Against Slab the Killer, need to miss => update stack atom
      Notifications::tell(clienttranslate('But ${player_name} needs another Missed!'), [
        'player_name' => $player->getName(),
      ]);
      $atom = Stack::top();
      $atom['missedNeeded'] = $missedNeeded - 1;
      Stack::insertAfter($atom);
      Stack::resolve();
    } else {
      Notifications::tell(clienttranslate('Barrel failed'));
    }

    //    Log::addCardPlayed(Players::getCurrentTurn(), Cards::getCurrentCard(), $args);
  }
}
