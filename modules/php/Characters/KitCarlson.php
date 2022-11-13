<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Helpers\Sounds;
use BANG\Managers\Cards;

class KitCarlson extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = KIT_CARLSON;
    $this->character_name = clienttranslate('Kit Carlson');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he looks at the top three cards of the deck: he chooses 2 to draw, and puts the other one back on the top of the deck, face down.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsPhaseOne()
  {
    $cards = Cards::drawForLocation(LOCATION_SELECTION, 3);
    $this->prepareSelection($this, [$this->id], true, 2);
  }

  public function useAbility($args)
  {
    // Move selected cards and notify
    foreach ($args as $cardId) {
      Cards::move($cardId, LOCATION_HAND, $this->id);
    }
    Notifications::drawCards($this, Cards::getMany($args));
    $this->onChangeHand();

    // Put remaining card on deck
    $rest = Cards::getSelection()->first();
    Notifications::playSound(Sounds::getSoundForCharacterAbility());
    Cards::putOnDeck($rest->getId());
  }
}
