<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Helpers\Sounds;

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
    Stack::insertAfterCardResolution(Stack::newAtom(ST_TRIGGER_ABILITY, [
      'pId' => $this->id,
      'amount' => $amount,
    ]));
  }

  public function useAbility($ctx)
  {
    $this->drawCards($ctx['amount']);
    Notifications::playSound(Sounds::getSoundForCharacterAbility());
  }
}
