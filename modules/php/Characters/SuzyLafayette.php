<?php
namespace BANG\Characters;
use BANG\Core\Stack;
use BANG\Managers\Rules;

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
    if ($this->getHand()->count() == 0 && Rules::isAbilityAvailable()) {
      Stack::insertAfterCardResolution(
        Stack::newSimpleAtom(ST_TRIGGER_ABILITY, $this),
        false
      );
    }
  }

  public function useAbility($ctx)
  {
    if ($this->getHand()->count() == 0) {
      $this->drawCards(1);
    }
  }
}
