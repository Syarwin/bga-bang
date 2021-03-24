<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Managers\Cards;
use bang;

class SidKetchum  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = SID_KETCHUM;
    $this->character_name = clienttranslate('Sid Ketchum');
    $this->text  = [
      clienttranslate("He may discard 2 cards to regain 1 life point"),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getHandOptions() {
    $res = parent::getHandOptions();
    if(count($this->getCardsinHand())>1)
        $res['character'] = SID_KETCHUM;
    return $res;
  }

  public function useAbility($args) {
    Notifications::tell(clienttranslate('${player_name} uses the ability of Sid Ketchum by discarding 2 cards to regain 1 life point'), ['player_name' => $this->name]);
    foreach ($args as $card) Cards::playCard($card);
    Notifications::discardedCards($this, array_map(['BANG\Cards\Cards','getCard'], $args));
    $this->gainLife();
    bang::get()->gamestate->nextState("continuePlaying");
  }
}
