<?php
namespace BANG\Characters;

class PaulRegret extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = PAUL_REGRET;
    $this->character_name = clienttranslate('Paul Regret');
    $this->text = [clienttranslate('All players see him at a distance +1')];
    $this->bullets = 3;
    parent::__construct($row);
  }

  public function getDistanceTo($enemy)
  {
    return parent::getDistanceTo($enemy) + 1;
  }
}
