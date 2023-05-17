<?php
namespace BANG\Characters;

use BANG\Managers\Rules;

class RoseDoolan extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = ROSE_DOOLAN;
    $this->character_name = clienttranslate('Rose Doolan');
    $this->text = [clienttranslate('She sees all players at distance -1')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function isInRange($enemy, $range)
  {
    if (Rules::isAbilityAvailable()) {
      return parent::isInRange($enemy, $range + 1);
    } else {
      return parent::isInRange($enemy, $range);
    }
  }

  public function getDistances()
  {
    $dist = parent::getDistances();
    if (Rules::isAbilityAvailable()) {
      foreach ($dist as $pId => &$d) {
        $d--;
      }
    }
    return $dist;
  }
}
