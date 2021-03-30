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

  public function playCard($card, $args)
  {
    if ($card->getType() == CARD_BANG) {
      $args['missedNeeded'] = 2;
    }
    return parent::playCard($card, $args);
  }
}
