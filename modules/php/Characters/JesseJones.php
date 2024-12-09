<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Managers\Rules;

class JesseJones extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = JESSE_JONES;
    $this->character_name = clienttranslate('Jesse Jones');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsPhaseOne()
  {
    // TODO : auto skip if argDrawCard only has 'deck' inside
    $ctx = Stack::getCtx();
    Stack::insertOnTop(Stack::newAtom(ST_ACTIVE_DRAW_CARD, [
      'pId' => $this->getId(),
      'storeResult' => isset($ctx['storeResult']) && $ctx['storeResult'],
    ]));
  }

  public function argDrawCard()
  {
    $options = Players::getLivingPlayers($this->id)
      ->filter(function ($player) {
        return $player->getHand()->count() > 0;
      })
      ->getIds();
    $options[] = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);
    return ['options' => $options];
  }

  public function useAbility($args)
  {
    if (in_array($args['selected'], [LOCATION_DECK, LOCATION_DISCARD])) {
      $location = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);
      $cards = Cards::deal($this->id, 1, $location);
      Notifications::drawCards($this, $cards, $location === LOCATION_DISCARD, $location);
      $card = $cards->first();
    } else {
      // TODO : add sanity check

      // Stole the first card
      $victim = Players::get($args['selected']);
      $card = $victim->getRandomCardInHand();
      Cards::move($card->getId(), LOCATION_HAND, $this->id);
      Notifications::stoleCard($this, $victim, $card, false);
      $victim->onChangeHand();
    }

    $ctx = Stack::getCtx();
    if (isset($ctx['storeResult']) && $ctx['storeResult']) {
      Stack::updatePhaseOneAtomAfterAction([$card->getId()]);
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
