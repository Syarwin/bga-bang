<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;

class PedroRamirez extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = PEDRO_RAMIREZ;
    $this->character_name = clienttranslate('Pedro Ramirez');
    $this->text = [
      clienttranslate(
        'During the first phase of his turn, he may choose to draw the first card from the top of the discard pile or from the deck. Then, he draws the second card from the deck.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsPhaseOne()
  {
    if (is_null(Cards::getLastDiscarded())) {
      Rules::incrementPhaseOneDrawEndAmount();
    } else {
      $ctx = Stack::getCtx();
      Stack::insertOnTop(Stack::newAtom(ST_ACTIVE_DRAW_CARD, [
        'pId' => $this->getId(),
        'storeResult' => isset($ctx['storeResult']) && $ctx['storeResult'],
      ]));
    }
  }

  public function argDrawCard()
  {
    $options = [LOCATION_DECK, LOCATION_DISCARD];
    return ['options' => $options];
  }

  public function useAbility($args)
  {
    if ($args['selected'] === LOCATION_DECK) {
      $cards = Cards::deal($this->id, 1);
      Notifications::drawCards($this, $cards);
    } else {
      $cards = Cards::dealFromDiscard($this->id, 1);
      Notifications::drawCardFromDiscard($this, $cards);
    }

    $ctx = Stack::getCtx();
    if (isset($ctx['storeResult']) && $ctx['storeResult']) {
      Stack::updatePhaseOneAtomAfterAction($cards->getIds());
    }
  }

  public function getPhaseOneRules($defaultAmount, $isAbilityAvailable = true)
  {
    if ($isAbilityAvailable) {
      return [
        RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 0,
        RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => true,
        RULE_PHASE_ONE_CARDS_DRAW_END => $defaultAmount - 1
      ];
    } else {
      return parent::getPhaseOneRules($defaultAmount);
    }
  }
}
