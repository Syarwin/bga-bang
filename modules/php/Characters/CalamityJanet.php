<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Managers\Cards;
use BANG\Cards\Bang;

class CalamityJanet  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = CALAMITY_JANET;
    $this->character_name = clienttranslate('Calamity Janet');
    $this->text  = [
      clienttranslate("She can play BANG! cards as Missed! cards and vice versa."),

    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function getBangCards() {
    $res = parent::getBangCards();
    $hand = Cards::getHand($this->id);
    foreach($hand as $card) {
      if($card->getType() == CARD_MISSED)
        $res['cards'][] = ['id' => $card->getId(), 'options' => ['type' => OPTION_NONE]];
    }
    return $res;
  }

  public function getDefensiveOptions() {
    $missed = parent::getDefensiveOptions();
    $args = Log::getLastAction('cardPlayed');
    $amount = isset($args['missedNeeded']) ? $args['missedNeeded'] : 1;
    $bangs = parent::getBangCards();
    foreach($bangs['cards'] as $card) {
      $card['amount'] = $amount;
      $missed['cards'][] = $card;
    }
    return $missed;
  }

  public function getHandOptions() {
    $res = parent::getHandOptions();
    $hand = Cards::getHand($this->id);
    $bang = new Bang();
    $options = $bang->getPlayOptions($this);
    foreach($hand as $card) {
      if($card->getType() == CARD_MISSED)
        $res['cards'][] = ['id' => $card->getID(), 'options' => $options];
    }
    return $res;
  }

  public function playCard($id, $args) {
    $card = Cards::get($id);
    if($card->getType() == CARD_MISSED) {
      $args['asBang'] = true;
      Notifications::cardPlayed($this, $card, $args);
      Log::addCardPlayed($this, $card, $args);
      $card = new Bang($id, "");
      $newstate = $card->play($this, $args);
      return $newstate;
    }
    return parent::playCard($id, $args);
  }

}
