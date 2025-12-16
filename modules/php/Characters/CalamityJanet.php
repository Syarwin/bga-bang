<?php

declare(strict_types=1);

namespace BANG\Characters;

use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Cards\Bang;
use BANG\Managers\Rules;
use BANG\Models\AbstractCard;
use BANG\Models\Player;

class CalamityJanet extends Player
{
  public function __construct(?array $row = null)
  {
    $this->character = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text = [clienttranslate('She can play BANG! cards as Missed! cards and vice versa.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getReactAtomForAttack(AbstractCard $card, ?int $targetCardId = null, bool $secondMissedNeeded = false): array
  {
    $atom = parent::getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded);
    if ($card->getType() == CARD_MISSED) {
      $atom['src_name'] = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    return $atom;
  }

  public function getBangCards(array $options = []): array
  {
    $res = parent::getBangCards();
    if (!Rules::isAbilityAvailable()) {
      return $res;
    }

    $hand = $this->getHand();
    foreach ($hand as $card) {
      if ($card->getType() !== CARD_MISSED) {
        continue;
      }
      $res['cards'][] = [
        'id' => $card->getId(),
        'options' => ['target_types' => [TARGET_NONE]],
        'amount' => 1,
      ];
    }
    return $res;
  }

  public function getDefensiveOptions(): array
  {
    $missed = parent::getDefensiveOptions();
    if (!Rules::isAbilityAvailable()) {
      return $missed;
    }
    $amount = Stack::top()['missedNeeded'] ?? 1;
    $bangs = parent::getBangCards();
    foreach ($bangs['cards'] as $card) {
      $card['amount'] = $amount;
      $missed['cards'][] = $card;
    }
    return $missed;
  }

  public function getHandOptions(): array
  {
    $res = parent::getHandOptions();
    if (!Rules::isAbilityAvailable()) {
      return $res;
    }
    $hand = $this->getHand();
    $bang = new Bang();
    $options = $bang->getPlayOptions($this);
    foreach ($hand as $card) {
      if ($card->getType() !== CARD_MISSED) {
        continue;
      }
      $mustPlayCardId = Globals::getMustPlayCardId();
      $cardId = $card->getId();
      $cardOptions = ['id' => $cardId, 'options' => $options];
      if (Globals::getIsMustPlayCard() && $mustPlayCardId !== 0) {
        $cardOptions['mustPlay'] = $cardId === $mustPlayCardId;
      }
      $res['cards'][] = $cardOptions;
    }
    return $res;
  }

  public function getBangCardTypes(): array
  {
    return array_merge(parent::getBangCardTypes(), [CARD_MISSED]);
  }

  public function playCard(AbstractCard $card, array $args): void
  {
    if ($card->getType() == CARD_MISSED) {
      $args['asBang'] = true;
      Notifications::cardPlayed($this, $card, $args);
      Log::addCardPlayed($this, $card, $args);
      $card = new Bang(['id' => $card->getId()]);
      $card->play($this, $args);
      $this->onChangeHand();
    } else {
      parent::playCard($card, $args);
    }
  }
}
