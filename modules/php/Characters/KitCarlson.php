<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;
use BANG\Managers\Rules;

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
    $location = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);
    $cards = Cards::drawForLocation(LOCATION_SELECTION, 3, $location);
    $eventCard = EventCards::getActive();
    $amountToDraw = $eventCard ? $eventCard->getPhaseOneAmountOfCardsToDraw() : 2;
    $this->prepareSelection($this, [$this->id], true, $amountToDraw);
    Notifications::drawCards($this, $cards, $location === LOCATION_DISCARD, $location, true, true);
  }

  public function useAbility($args)
  {
    $location = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);

    // Put remaining cards on deck
    $rest = Cards::getSelection()->filter(function ($card) use ($args) {
      return !in_array($card->getId(), $args);
    });
    if ($rest) {
      $this->putCardsBack(array_reverse($rest->toArray()), $location);
    }
    $this->putCardsBack(Cards::getMany($args)->toArray(), $location);

    $ctx = Stack::getCtx();
    if (isset($ctx['storeResult']) && $ctx['storeResult']) {
      Stack::updatePhaseOneAtomAfterAction([]);
    }
  }

  private function putCardsBack($cards, $location)
  {
    foreach ($cards as $card) {
      if ($location === LOCATION_DECK) {
        Cards::putOnDeck($card->getId());
        Notifications::discardedCardToDeck($this, $card, true);
      } else {
        Cards::play($card->getId());
        Notifications::discardedCard($this, $card, true);
      }
    }
  }

  public function getPhaseOneRules($defaultAmount, $isAbilityAvailable = true)
  {
    if ($isAbilityAvailable) {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 0,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => $defaultAmount
      ];
    } else {
      return parent::getPhaseOneRules($defaultAmount);
    }
  }
}
