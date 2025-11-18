<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Managers\Players;
use BANG\Core\Stack;
use BANG\Models\AbstractCard;
use BANG\Models\BrownCard;
use BANG\Models\Player;

class Duel extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_DUEL;
    $this->name = clienttranslate('Duel');
    $this->text = clienttranslate(
      'A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.'
    );
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['QD', 'JS', '8C'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 0,
      'impacts' => ANY,
    ];
  }

  public function getPlayOptions(Player $player): ?array
  {
    $livings = Players::getLivingPlayers($player->getId());
    return [
      'target_types' => [TARGET_PLAYER],
      'targets' => $livings->getIds(),
    ];
  }

  public function play(Player $player, array $args): void
  {
    parent::play($player, $args);
    $atom = Stack::newAtom(ST_REACT, [
      'type' => REACT_TYPE_DUEL,
      'msgActive' => clienttranslate('${you} may react to the duel by discarding a Bang!'),
      'msgInactive' => clienttranslate('${actplayer} may react to the duel by discarding a Bang!'),
      'src' => $this->jsonSerialize(),
      'attacker' => $player->getId(),
      'opponent' => $args['player'],
      'pId' => $args['player'],
    ]);

    Stack::insertOnTop($atom);
  }

  public function getReactionOptions(Player $player): array
  {
    return $player->getBangCards();
  }

  public function pass(Player $player): void
  {
    $player->loseLife();
  }

  public function react(AbstractCard $card, Player $player): void
  {
    $player->discardCard($card);

    // Get top of the stack, change pId and insertAfter
    $atom = Stack::top();
    $atom['pId'] = $atom['pId'] == $atom['attacker'] ? $atom['opponent'] : $atom['attacker'];
    Stack::insertAfter($atom);
  }
}
