<?php

namespace BANG\Cards;

use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Notifications;
use BANG\Managers\Rules;
use BANG\Models\BrownCard;
use BANG\Models\Player;
use BgaVisibleSystemException;

class Beer extends BrownCard
{
  public function __construct(?array $params = null)
  {
    parent::__construct($params);
    $this->type = CARD_BEER;
    $this->name = clienttranslate('Beer');
    $this->text = clienttranslate('Regain one life point.');
    $this->symbols = [[SYMBOL_LIFEPOINT]];
    $this->copies = [
      BASE_GAME => ['6H', '7H', '8H', '9H', '10H', 'JH'],
      HIGH_NOON => [],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => LIFE_POINT_MODIFIER,
      'amount' => 1,
      'impacts' => NONE,
    ];
  }

  public function play(Player $player, array $args): void
  {
    if (!Rules::isBeerAvailable()) {
      throw new BgaVisibleSystemException('Error: Beer was playable but not available at the same time. Please report this to BGA bug tracker');
    }
    if (count(Players::getLivingPlayers()) <= 2) {
      Cards::discard($this);
      Notifications::tell(clienttranslate('Beer has no effect when only 2 players are alive'));
    } else {
      if ($player->getBullets() == $player->getHp()) {
        Notifications::tell(clienttranslate('Beer had no effect because ${player_name} has maximum amount of life points'), ['player' => $player]);
      }
      parent::play($player, $args);
    }
  }

  public function getPlayOptions(Player $player): ?array
  {
    if (!Rules::isBeerAvailable()) {
      return null;
    }

    $options = parent::getPlayOptions($player);
    if ($options !== null && $player->getBullets() === $player->getHp()) {
      $msg = clienttranslate('You have maximum amount of life points. Drinking a beer would currently have no effect. Do you still want to drink it?');
      $options['confirmationMsg'] = $msg;
    }
    if ($options !== null && count(Players::getLivingPlayers()) <= 2) {
      $msg = clienttranslate('Drinking a beer when only 2 players are left have no effect. Do you still want to drink it?');
      $options['confirmationMsg'] = $msg;
    }
    return $options;
  }
}
