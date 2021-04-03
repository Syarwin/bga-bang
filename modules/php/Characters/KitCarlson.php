<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;

class KitCarlson extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = KIT_CARLSON;
    $this->character_name = clienttranslate('Kit Carlson');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down. '
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function statePhaseOne()
  {
    $id = $this->id;
    Cards::createSelection(3, $id);
    Log::addAction('selection', ['players' => [$id, $id], 'src' => $this->character_name]);
    return 'selection';
  }

  public function useAbility($args)
  {
    foreach ($args['selected'] as $card) {
      Cards::moveCard($card, 'hand', $this->id);
    }
    Cards::putOnDeck($args['rest'][0]);
    Notifications::drawCards($this, Cards::getCards($args['selected']));

    // TODO notification
    return 'play';
  }
}
