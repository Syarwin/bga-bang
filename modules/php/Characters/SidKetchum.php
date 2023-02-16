<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

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
    if ($this->countHand() > 1 && Rules::isAbilityAvailable()) {
      $t['character'] = SID_KETCHUM;
    }
    return $t;
  }

  public function getHandOptions($lastCardOnly = false)
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

    $cards = Cards::getMany($args);
    Cards::discardMany($args);
    Notifications::discardedCards($this, $cards, false, $args);
    $this->gainLife();
    $this->addRevivalAtomOrEliminate();
  }
}
