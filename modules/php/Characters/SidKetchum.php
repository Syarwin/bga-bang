<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;

// TODO : make its ability available (almost) at any time
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

  protected function addAbility($t)
  {
    if ($this->countHand() > 1) {
      $t['character'] = SID_KETCHUM;
    }
    return $t;
  }

  public function getHandOptions()
  {
    return $this->addAbility(parent::getHandOptions());
  }

  public function getBeerOptions()
  {
    return $this->addAbility(parent::getBeerOptions());
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

    // If we were in the end of life state and now have enough life point, proceed to next state
    if (Stack::top()['state'] == ST_REACT_BEER && $this->hp > 0) {
      Stack::nextState();
    } else {
      // Otherwise loop back in same state
      Stack::resolve();
    }
  }
}
