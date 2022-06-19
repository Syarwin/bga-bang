<?php
namespace BANG\Cards;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Notifications;

class Beer extends \BANG\Models\BrownCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
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

  public function play($player, $args)
  {
    if (count(Players::getLivingPlayers()) <= 2) {
      Cards::discard($this);
      Notifications::tell(clienttranslate('Beer has no effect when only 2 players are alive.'));
    } else {
      parent::play($player, $args);
    }
  }

  public function getPlayOptions($player)
  {
    $options = parent::getPlayOptions($player);
    if ($options != null && $player->getBullets() == $player->getHp()) {
      $msg = clienttranslate('You have maximum amount of life points. Drinking a beer would currently have no effect. Do you still want to drink it?');
      $options['confirmationMsg'] = $msg;
    }
    if ($options != null && Players::count() == 2) {
      $msg = clienttranslate('Drinking a beer when only 2 players are left have no effect. Do you still want to drink it?');
      $options['confirmationMsg'] = $msg;
    }
    return $options;
  }
}
