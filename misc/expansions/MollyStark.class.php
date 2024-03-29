<?php

class MollyStark  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = MOLLY_STARK;
    $this->character_name = clienttranslate('Molly Stark');
    $this->text  = [
      clienttranslate("Each time she plays or voluntarily discards a card when it is not her turn (e.g. Missed!, Beer, or BANG! during
Indians!), she draws one card from the deck."),
      clienttranslate("If she discards a BANG! during a Duel, she does not draw her replacement cards until the end of the Duel, when she would draw one card for each BANG! she used during the Duel."),
      clienttranslate("Cards that she is forced to discard due to cards like Cat Balou, Brawl, or Can-Can are not considered voluntarily discarded!."),
    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;
    parent::__construct($row);
  }
}