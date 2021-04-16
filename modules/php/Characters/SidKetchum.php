<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;

class SidKetchum extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = SID_KETCHUM;
    $this->character_name = clienttranslate('Sid Ketchum');
    $this->text = [clienttranslate('He may discard 2 cards to regain 1 life point')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getHandOptions()
  {
    $res = parent::getHandOptions();
    if ($this->countHand() > 1) {
      $res['character'] = SID_KETCHUM;
    }
    return $res;
  }

  public function useAbility($args)
  {
    Notifications::tell(
      clienttranslate('${player_name} uses the ability of Sid Ketchum by discarding 2 cards to regain 1 life point'),
      ['player_name' => $this->name]
    );

    $cards = Cards::get($args);
    foreach ($cards as $card) {
      $card->discard();
    }
    Notifications::discardedCards($this, $cards);
    $this->gainLife();
    Stack::resolve(); // Loop back in same state
  }
}
