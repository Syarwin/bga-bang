<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;

class PedroRamirez  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text  = [
      clienttranslate("During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck. "),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function statePhaseOne() {
    $locations = ['deck'];
    if(!is_null(Cards::getLastDiscarded()))
      array_push($locations, 'discard');
    Log::addAction("draw", $locations);
    return 'activeDraw';
  }

  public function useAbility($args) {
    $cards = [];
    if($args['selected'] == 'deck') {
      $cards = Cards::deal($this->id, 2);
      Notifications::drawCards($this, $cards);
    } else {
      // Draw the first one from discard
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);
      // The second one from deck
      $cards = Cards::deal($this->id, 1);
      Notifications::drawCards($this, $cards);
    }
    return "play";
  }

}
