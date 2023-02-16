<?php
namespace BANG\Cards\Events;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class LawOfTheWest extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_LAW_OF_THE_WEST;
    $this->name = clienttranslate('Law Of The West');
    $this->text = clienttranslate('Players must show and play (if possible) the second card they draw in their turn.');
    $this->effect = EFFECT_PHASE_ONE;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function getPhaseOneAmountOfCardsToDraw()
  {
    return 1;
  }

  /**
   * @return array
   */
  public function getPhaseOneRules()
  {
    return [RULE_PHASE_ONE_EVENT_SPECIAL_DRAW => true] + parent::getPhaseOneRules();
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
    Notifications::drawCards($player, $cards, true);
    $atom = Stack::newSimpleAtom(ST_PLAY_LAST_CARD_AUTOMATICALLY, $player->getId());
    Stack::insertOnTop($atom);
  }
}
