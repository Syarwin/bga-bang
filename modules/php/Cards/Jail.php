<?php
namespace BANG\Cards;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Managers\Cards;

class Jail extends \BANG\Models\BlueCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id);
    $this->type = CARD_JAIL;
    $this->name = clienttranslate('Jail');
    $this->text = clienttranslate(
      "Equip any player with this. At the start of that players turn reveal top card from the deck. If it's not heart that player is skipped. Either way, the jail is discarded."
    );
    $this->symbols = [
      [
        SYMBOL_DRAW_HEART,
        clienttranslate('Discard the Jail, play normally. Else discard the Jail and skip your turn.'),
      ],
    ];
    $this->copies = [
      BASE_GAME => ['JS', '4H', '10S'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
  }

  public function getPlayOptions($player)
  {
    // Can be played on anyone except the sheriff
    $players = Players::getLivingPlayers()->filter(function ($player) {
      return $player->getRole() != SHERIFF && !$player->hasCardCopyInPlay($this);
    });
    return [
      'target_type' => TARGET_PLAYER,
      'targets' => $players->getIds(),
    ];
  }

  public function play($player, $args)
  {
    Cards::equip($this->id, $args['player']);
  }

  public function startOfTurn($player)
  {
    $player->addFlipAtom($this);
  }

  public function resolveFlipped($card, $player)
  {
    $player->discardCard($card, true); // Discard a flipped card
    $player->discardCard($this, true); // Discard Jail itself

    $args = ['player' => $player, 'event' => null];
    if ($card->getCopyColor($args['event']) == 'H') {
      Notifications::tell(clienttranslate('${player_name} can make his turn${flipEventMsg}'), $args);
    } else {
      if ($card->getSuit() !== 'H') { //result changed because of event?
        $args['event'] = null;
      }
      Notifications::tell(clienttranslate('${player_name} is skipped${flipEventMsg}'), $args);
      Stack::clearAllLeaveLast();
    }
  }
}
