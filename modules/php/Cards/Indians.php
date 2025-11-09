<?php

namespace BANG\Cards;

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

  public function getReactionOptions($player)
  {
    return $player->getBangCards();
  }

  public function pass($player)
  {
    $player->loseLife();
  }

  public function react($card, $player)
  {
    $player->discardCard($card);
  }
}
