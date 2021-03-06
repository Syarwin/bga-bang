<?php
namespace Bang\Characters;
use Bang\Cards\Cards;

class SlabtheKiller extends Player {
  public function __construct($row = null)
  {
    $this->character    = SLAB_THE_KILLER;
    $this->character_name = clienttranslate('Slab the Killer');
    $this->text  = [
      clienttranslate("Players trying to cancel his BANG! cards need to play 2 Missed! "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function playCard($id, $args) {
		$card = Cards::getCard($id);
    if($card->getType() == CARD_BANG) $args['missedNeeded'] = 2;
    return parent::playCard($id, $args);
	}
}
