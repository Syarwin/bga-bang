<?php
namespace BANG\Characters;

class BartCassidy extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = BART_CASSIDY;
    $this->character_name = clienttranslate('Bart Cassidy');
    $this->text = [clienttranslate('Each time he looses a life point, he immediately draws a card from the deck. ')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  // TODO : make it work with the dynamite : should lost the three hp THEN draw three cards if not dead
  public function looseLife($amount = 1)
  {
    // TODO
    //$this->registerAbility(['amount'=>$amount]);
    //$newstate = parent::looseLife($amount);
    //if(!$this->eliminated) $this->drawCards($amount);

    // return $newstate;
    parent::looseLife($amount);
  }

  public function useAbility($args)
  {
    $this->drawCards($args['amount']);
  }
}
