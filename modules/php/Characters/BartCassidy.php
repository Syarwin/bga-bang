<?php
namespace BANG\Characters;
use BANG\Core\Stack;
use BANG\Managers\Rules;

class BartCassidy extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = BART_CASSIDY;
    $this->character_name = clienttranslate('Bart Cassidy');
    $this->text = [clienttranslate('Each time he loses a life point, he immediately draws a card from the deck.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function loseLife($amount = 1)
  {
    parent::loseLife($amount);
    if (Rules::isAbilityAvailable()) {
      Stack::insertAfterCardResolution(Stack::newAtom(ST_TRIGGER_ABILITY, [
        'pId' => $this->id,
        'amount' => $amount,
      ]), false);
    }
  }

  public function useAbility($ctx)
  {
    $this->drawCards($ctx['amount']);
  }
}
