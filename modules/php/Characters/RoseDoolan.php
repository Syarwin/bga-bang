<?php
namespace BANG\Characters;

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
    return parent::isInRange($enemy, $range + 1);
  }

  public function getDistances()
  {
    $dist = parent::getDistances();
    foreach ($dist as $pId => &$dist) {
      $dist--;
    }
    return $dist;
  }
}
