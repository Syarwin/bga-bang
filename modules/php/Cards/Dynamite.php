<?php
namespace BANG\Cards;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;

class Dynamite extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_DYNAMITE;
    $this->name = clienttranslate('Dynamite');
    $this->text = clienttranslate(
      "At the start of your turn reveal top card from the deck. If it's Spades 2-9, you lose 3 life points. Else pass the Dynamite to the player on your left."
    );
    $this->symbols = [[SYMBOL_DYNAMITE, clienttranslate('Lose 3 life points. Else pass the Dynamite on your left.')]];
    $this->copies = [
      BASE_GAME => ['2H'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
  }

  /*
   * When activated at the start of turn, flip a card and resolve effect
   */
  public function startOfTurn($player)
  {
    $player->addFlipAtom($this);
  }

  public function resolveFlipped($card, $player)
  {
    $player->discardCard($card, true); // Discard a flipped card

    $copyValue = $card->getCopyValue();
    $event = null;
    // Between 2 & 9 of spades ? => kaboom
    if ($card->getCopyColor($event) == 'S' && is_numeric($copyValue) && intval($copyValue) < 10) {
      Notifications::tell(clienttranslate('Dynamite explodes${flipEventMsg}'), ['event' => $event]);
      $player->discardCard($this, true); // Discard Dynamite itself
      $player->loseLife(3);
    } else {
      // TODO : move to next player WITHOUT a dynamite (not needed for base game)
      $next = Players::getNext($player);
      Cards::equip($this->id, $next->getId());
      Notifications::moveCard($this, $player, $next);
    }
  }

  public function play($player, $args)
  {
    Cards::equip($this->id, $player->getId());
  }
}
