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
    if ($this->getHand()->count() == 0 && Rules::isAbilityAvailable() && $this->hp > 0) {
      $ctx = Stack::getCtx();
      $newAtom = Stack::newSimpleAtom(ST_TRIGGER_ABILITY, $this);
      if ($ctx['state'] === ST_REACT && array_key_exists('missedNeeded', $ctx) && $ctx['missedNeeded'] > 1) {
        Stack::insertOnTop($newAtom);
      } else {
        Stack::insertAfterCardResolution($newAtom, false);
      }
    }
  }

  public function useAbility()
  {
    if ($this->getHand()->count() == 0) {
      $this->drawCards(1);
    }
  }

  /**
   * {{@inheritDoc}}
   */
  public function getDefensiveOptions()
  {
    $options = parent::getDefensiveOptions();
    if ($this->getHand()->count() == 1 && isset($options['cards'][0]['amount'])) {
      $options['cards'][0]['amount'] = 1;
    }

    return $options;
  }
}
