<?php
namespace BANG\Cards;
use BANG\Managers\Players;
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
      Cards::discardCard($this);
      Notifications::tell(clienttranslate('Beer has no effect when only 2 players are alive.'));
    } else {
      parent::play($player, $args);
    }
  }
}
