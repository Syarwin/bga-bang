<?php
namespace Bang\Characters;
use Bang\Game\Notifications;
use Bang\Game\Log;
use Bang\Game\Utils;
use Bang\Cards\Cards;

class KitCarlson extends Player {
  public function __construct($row = null)
  {
    $this->character    = KIT_CARLSON;
    $this->character_name = clienttranslate('Kit Carlson');
    $this->text  = [
      clienttranslate("During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function statePhaseOne() {
    $id = $this->id;
    Cards::createSelection(3, $id);
    Log::addAction("selection", ['players' => [$id, $id], 'src' => $this->character_name]);
    return 'selection';
  }

  public function useAbility($args) {
    foreach ($args['selected'] as $card)
      Cards::moveCard($card, 'hand', $this->id);
    Cards::putOnDeck($args['rest'][0]);
    Notifications::drawCards($this, Cards::getCards($args['selected']));

    // TODO notification
    return "play";
  }

}
