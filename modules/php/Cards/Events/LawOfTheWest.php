<?php
namespace BANG\Cards\Events;
use BANG\Core\Globals;
use BANG\Core\Notifications;
use BANG\Managers\Cards;
use BANG\Models\AbstractCard;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

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

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 1;
  }

  /**
   * @return array
   */
  public function getRules()
  {
    return [RULE_PHASE_ONE_EVENT_SPECIAL_DRAW => true] + parent::getRules();
  }

  /**
   * @return boolean
   */
  public function isPhaseOneSpecialDraw()
  {
    return true;
  }

  /**
   * @param Player $player
   * @return void
   */
  public function drawCardsPhaseOne($player)
  {
    $cards = Cards::deal($player->getId(), 1);
    Globals::setMustPlayCardId($cards->first()->getId());
    Notifications::drawCards($player, $cards, true);
  }

  /**
   * @param AbstractCard $card
   * @param Player $player
   * @return void
   */
  public function resolveEffect($player = null)
  {
    $card = Cards::get(Globals::getMustPlayCardId());
    $cardsInPlayTypes = $player->getCardsInPlay()->map(function ($card) {
      return $card->getType();
    });
    $inRangeOfWeapon = $player->getPlayersInRange();
    $inRangeOfWeapon = array_diff($inRangeOfWeapon, [$player->getId()]);
    $inSpecificRange = isset($card->getEffect()['range']) ? $player->getPlayersInRange($card->getEffect()['range']) : [];
    $cardImpacts = $card->getEffect()['impacts'] ?? null;
    Globals::setIsMustPlayCard($card->getType() !== CARD_MISSED
      && !$cardsInPlayTypes->contains($card->getType())
      && !($cardImpacts === INRANGE && empty($inRangeOfWeapon))
      && !($cardImpacts === SPECIFIC_RANGE && empty($inSpecificRange)));
  }
}
