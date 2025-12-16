<?php

declare(strict_types=1);

namespace BANG\Cards;

use BANG\Models\AbstractCard;
use BANG\Models\BrownCard;
use BANG\Models\Player;

class Indians extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_INDIANS;
    $this->name = clienttranslate('Indians!');
    $this->text = clienttranslate('All other players discard a BANG! or lose 1 life point.');
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['KD', 'AD'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 0,
      'impacts' => ALL_OTHER,
    ];
  }

  public function play(Player $player, array $args): void
  {
    parent::play($player, $args);
    $ids = $player->getOrderedOtherPlayers();
    $player->attack($this, $ids);
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
  }
}
