<?php
namespace BANG\Cards;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Managers\Players;
use BANG\Managers\Cards;

class GeneralStore extends \BANG\Models\BrownCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_GENERAL_STORE;
    $this->name = clienttranslate('General Store');
    $this->text = clienttranslate('Reveal as many cards as players left. Each player chooses one, starting with you');
    $this->symbols = [[clienttranslate('Reveal as many card as players. Each player draws one.')]];
    $this->copies = [
      BASE_GAME => ['9C', 'QS'],
      DODGE_CITY => [],
    ];
    $this->effect = ['type' => OTHER];
  }

  public function play($player, $args)
  {
    parent::play($player, $args);
    $players = Players::getLivingPlayersStartingWith($player);
    Log::addAction('selection', ['players' => $players, 'src' => $this->name, 'card' => 1]);
    $cards = Cards::createSelection(count($players));
    $player->prepareSelection($this, $cards, $players);
    return 'selection';
  }

  public function react($card, $player)
  {
    Cards::move($card->getId(), 'hand', $player->getId());
    Notifications::chooseCard($player, $card);
    return null;
  }
}
