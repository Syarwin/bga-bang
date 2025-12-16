<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\AbstractCard;
use BANG\Models\BlueCard;
use BANG\Models\Player;

class Jail extends BlueCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
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

  public function getPlayOptions(Player $player): ?array
  {
    if (!Rules::isCanPlayBlueGreenCards()) {
      return null;
    }
    // Can be played on anyone except the sheriff
    $players = Players::getLivingPlayers()->filter(function (Player $player) {
      return $player->getRole() !== SHERIFF && !$player->hasCardCopyInPlay($this);
    });
    return [
      'target_types' => [TARGET_PLAYER],
      'targets' => $players->getIds(),
    ];
  }

  public function play(Player $player, array $args): void
  {
    Cards::equip($this->id, $args['player']);
  }

  public function startOfTurn(Player $player): void
  {
    $player->addFlipAtom($this);
  }

  public function resolveFlipped(AbstractCard $card, Player $player): void
  {
    $isIgnoreCardsInPlay = Rules::isIgnoreCardsInPlay();
    $player->discardCard($card, true); // Discard a flipped card
    if (!$isIgnoreCardsInPlay) $player->discardCard($this, true); // Discard Jail itself

    $suitOverrideInfo = Rules::getSuitOverrideInfo($card, 'H');
    $args = array_merge($suitOverrideInfo, ['player' => $player]);
    if ($suitOverrideInfo['flipSuccessful'] || Rules::isIgnoreCardsInPlay()) {
      Notifications::tell(clienttranslate('${player_name} can make his turn${flipEventMsg}'), $args);
    } else {
      Notifications::tell(clienttranslate('${player_name} is skipped${flipEventMsg}'), $args);
      Stack::clearAllLeaveLast();
    }
  }
}
