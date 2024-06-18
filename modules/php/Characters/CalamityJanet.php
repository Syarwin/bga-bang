<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Cards\Bang;
use BANG\Managers\Rules;

class CalamityJanet extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text = [clienttranslate('She can play BANG! cards as Missed! cards and vice versa.')];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded)
  {
    $atom = parent::getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded);
    if ($card->getType() == CARD_MISSED) {
      $atom['src_name'] = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    return $atom;
  }

  public function getBangCards($targetType = TARGET_NONE)
  {
    $res = parent::getBangCards();
    if (Rules::isAbilityAvailable()) {
      $hand = Cards::getHand($this->id);
      foreach ($hand as $card) {
        if ($card->getType() == CARD_MISSED) {
          $res['cards'][] = [
            'id' => $card->getId(),
            'options' => ['target_types' => [TARGET_NONE]],
            'amount' => 1,
          ];
        }
      }
    }
    return $res;
  }

  public function getDefensiveOptions()
  {
    $missed = parent::getDefensiveOptions();
    if (Rules::isAbilityAvailable()) {
      $amount = Stack::top()['missedNeeded'] ?? 1;
      $bangs = parent::getBangCards();
      foreach ($bangs['cards'] as $card) {
        $card['amount'] = $amount;
        $missed['cards'][] = $card;
      }
    }
    return $missed;
  }

  // TODO: Properly support Law of the West event
  public function getHandOptions($lastCardOnly = false)
  {
    $res = parent::getHandOptions();
    if (Rules::isAbilityAvailable()) {
      $hand = Cards::getHand($this->id);
      $bang = new Bang();
      $options = $bang->getPlayOptions($this);
      foreach ($hand as $card) {
        if ($card->getType() == CARD_MISSED) {
          $res['cards'][] = ['id' => $card->getID(), 'options' => $options];
        }
      }
    }
    return $res;
  }

  public function playCard($card, $args)
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
