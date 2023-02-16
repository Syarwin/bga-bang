<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;

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
    Cards::drawForLocation(LOCATION_SELECTION, 3);
    $eventCard = EventCards::getActive();
    $amountToDraw = $eventCard ? $eventCard->getPhaseOneAmountOfCardsToDraw() : 2;
    $this->prepareSelection($this, [$this->id], true, $amountToDraw);
  }

  public function useAbility($args)
  {
    // Move selected cards and notify
    foreach ($args as $cardId) {
      Cards::move($cardId, LOCATION_HAND, $this->id);
    }
    Notifications::drawCards($this, Cards::getMany($args));
    $this->onChangeHand();

    // Put remaining cards on deck
    // TODO: Add ability to choose the order
    $rest = Cards::getSelection();
    if ($rest) {
      foreach ($rest->toArray() as $card) {
        // TODO: Notify about returning card. We have a bug here, number of cards is incorrect
        Cards::putOnDeck($card->getId());
      }
    }
  }

  public function getPhaseOneRules($defaultAmount, $isAbilityAvailable = true)
  {
    if ($isAbilityAvailable) {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 0,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => 0
      ];
    } else {
      return parent::getPhaseOneRules($defaultAmount);
    }
  }
}
