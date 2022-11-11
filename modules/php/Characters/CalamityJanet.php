<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Cards\Bang;

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

  public function getReactAtomForAttack($card)
  {
    $atom = parent::getReactAtomForAttack($card);
    if ($card->getType() == CARD_MISSED) {
      $atom['src_name'] = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    return $atom;
  }

  public function getBangCards()
  {
    $res = parent::getBangCards();
    $hand = Cards::getHand($this->id);
    foreach ($hand as $card) {
      if ($card->getType() == CARD_MISSED) {
        $res['cards'][] = [
          'id' => $card->getId(),
          'options' => ['target_type' => TARGET_NONE],
          'amount' => 1,
        ];
      }
    }
    return $res;
  }

  public function getDefensiveOptions()
  {
    $missed = parent::getDefensiveOptions();
    $amount = Stack::top()['missedNeeded'] ?? 1;
    $bangs = parent::getBangCards();
    foreach ($bangs['cards'] as $card) {
      $card['amount'] = $amount;
      $missed['cards'][] = $card;
    }
    return $missed;
  }

  public function getHandOptions()
  {
    $res = parent::getHandOptions();
    $hand = Cards::getHand($this->id);
    $bang = new Bang();
    $options = $bang->getPlayOptions($this);
    foreach ($hand as $card) {
      if ($card->getType() == CARD_MISSED) {
        $res['cards'][] = ['id' => $card->getID(), 'options' => $options];
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
      $newstate = $card->play($this, $args);
      $this->onChangeHand();
      return $newstate;
    }
    return parent::playCard($card, $args);
  }
}
