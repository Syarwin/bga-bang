<?php

declare(strict_types=1);

namespace BANG\Cards\Events;

use BANG\Core\Globals;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Rules;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;
use BgaVisibleSystemException;

class LawOfTheWest extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_LAW_OF_THE_WEST;
    $this->name = clienttranslate('Law Of The West');
    $this->text = clienttranslate('During his phase 1, each player shows the second card he draws: if he can, he must play it during his phase 2.');
    $this->effect = EFFECT_BEFORE_EACH_PLAY_CARD;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function getRules(): array
  {
    return parent::getRules() + [
      RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => 1,
    ];
  }

  public function isPhaseOneSpecialDraw(): bool
  {
    return true;
  }

  public function drawCardsPhaseOne(Player $player): void
  {
    $ctx = Stack::getCtx();
    // Looks like a character already have drawn something!
    if (isset($ctx['cardsDrawnIds'])) {
      if (count($ctx['cardsDrawnIds']) <= 1) {
        if (count($ctx['cardsDrawnIds']) === 0) {
          $player->drawCards(1);
        }
        Globals::setMustPlayCardId($this->drawACardPublicly($player));
        Rules::amendRules([RULE_PHASE_ONE_CARDS_DRAW_END => 0]);
      } else if (count($ctx['cardsDrawnIds']) === 2) {
        Globals::setMustPlayCardId($ctx['cardsDrawnIds'][1]);
      } else {
        throw new BgaVisibleSystemException('Incorrect amount of cards drawn before Law Of The West: ' . count($ctx['cardsDrawnIds']));
      }
    } else {
      Globals::setMustPlayCardId($this->drawACardPublicly($player));
    }
  }

  private function drawACardPublicly(Player $player): int
  {
    $cards = $player->drawCards(1, true);
    return $cards->first()->getId();
  }

  public function resolveEffect(Player $player): void
  {
    if (Globals::getMustPlayCardId() !== 0) {
      $card = Cards::get(Globals::getMustPlayCardId());
      $cardsInPlayTypes = $player->getCardsInPlay()->map(function ($card) {
        return $card->getType();
      });
      $inRangeOfWeapon = $player->getPlayersInRange();
      $inRangeOfWeapon = array_diff($inRangeOfWeapon, [$player->getId()]);
      $inSpecificRange = isset($card->getEffect()['range']) ?
        $player->getPlayersInRange($card->getEffect()['range']) :
        [];
      $cardImpacts = $card->getEffect()['impacts'] ?? null;
      $cardType = $card->getType();
      if ($player->isCharacter(CALAMITY_JANET) && $cardType === CARD_MISSED) {
        $cardType = CARD_BANG;
        $cardImpacts = INRANGE;
      }
      Globals::setIsMustPlayCard(
        $cardType !== CARD_MISSED
        && !$cardsInPlayTypes->contains($cardType)
        && !($cardImpacts === INRANGE && empty($inRangeOfWeapon))
        && !($cardImpacts === SPECIFIC_RANGE && empty($inSpecificRange))
      );
    }
  }
}
