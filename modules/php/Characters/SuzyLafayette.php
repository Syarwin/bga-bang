<?php
namespace BANG\Characters;
use BANG\Core\Stack;

class SuzyLafayette extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = SUZY_LAFAYETTE;
    $this->character_name = clienttranslate('Suzy Lafayette');
    $this->text = [clienttranslate('As soon as she has no cards in her hand, she draws a card from the draw pile.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function checkHand()
  {
    if ($this->getHand()->count() == 0) {
      $ctx = Stack::getCtx();
      $newAtom = Stack::newAtom(ST_TRIGGER_ABILITY, [
        'pId' => $this->id,
      ]);
      if ($ctx['state'] === ST_REACT && $ctx['missedNeeded'] > 1) {
        Stack::insertOnTop($newAtom);
      } else {
        Stack::insertAfterCardResolution($newAtom, false);
      }
    }
  }

  public function useAbility($ctx)
  {
    if ($this->getHand()->count() == 0) {
      $this->drawCards(1);
    }
  }
}
