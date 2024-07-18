<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

class BlackJack extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = BLACK_JACK;
    $this->character_name = clienttranslate('Black Jack');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he must show the second card he draws: if it’s Heart or Diamonds, he draws one additional card.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsPhaseOne()
  {
    // Draw one visible
    $location = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);
    $cards = Cards::deal($this->id, 1, $location);
    Notifications::drawCards($this, $cards, true, $location);

    // If heart or diamond => draw again a private one
    $card = $cards->first();
    $suitOverrideInfo = Rules::getSuitOverrideInfo($card, ['H', 'D']);
    if ($suitOverrideInfo['eventChangedResult']) {
      $msg = $suitOverrideInfo['flipSuccessful'] ? 'Black Jack effect was successful${flipEventMsg}' : 'Black Jack effect failed${flipEventMsg}';
      Notifications::tell(clienttranslate($msg), $suitOverrideInfo);
    }

    if ($suitOverrideInfo['flipSuccessful']) {
      Rules::incrementPhaseOneDrawEndAmount();
    }
    $this->onChangeHand();

    $ctx = Stack::getCtx();
    if (isset($ctx['storeResult']) && $ctx['storeResult']) {
      // We put 0 as a first id because we know the only card which needs this is Law Of The West,
      // and it won't care about the first one but just a second. If this ever changes - we need a real first card id
      Stack::updatePhaseOneAtomAfterAction([0, $card->getId()]);
    }
  }

  public function getPhaseOneRules($defaultAmount, $isAbilityAvailable = true)
  {
    if ($defaultAmount === 1 || !$isAbilityAvailable) {
      return parent::getPhaseOneRules($defaultAmount);
    } else {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 1,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => $defaultAmount - 2
      ];
    }
  }
}
