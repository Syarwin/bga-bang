<?php
namespace BANG\Characters;
use BANG\Managers\Cards;

class SlabtheKiller extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = SLAB_THE_KILLER;
    $this->character_name = clienttranslate('Slab the Killer');
    $this->text = [clienttranslate('Players trying to cancel his BANG! cards need to play 2 Missed! ')];
    $this->bullets = 4;
    parent::__construct($row);
  }


  public function getReactAtomForAttack($card)
  {
    $atom = parent::getReactAtomForAttack($card);
    if ($card->getType() == CARD_BANG) {
      $missedNeeded = 2; // Slab's ability
      $atom['missedNeeded'] = $missedNeeded;
      $atom['msgActive'] = clienttranslate('${you} may react to ${src_name} with ${missedNeeded} Missed!');
      $atom['msgInactive'] = clienttranslate('${actplayer} may react to ${src_name} with ${missedNeeded} Missed!');
    }
    return $atom;
  }
}
