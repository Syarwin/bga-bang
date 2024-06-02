<?php
namespace BANG\Cards\Events;
use BANG\Cards\Bang;
use BANG\Core\Stack;
use BANG\Models\AbstractEventCard;
use BANG\Models\Player;

class FistfulOfCards extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_FISTFUL_OF_CARDS;
    $this->name = clienttranslate('A Fistful Of Cards');
    $this->text = clienttranslate('At the beginning of his turn, the player is the target of as many BANG! as the number of cards in his hand.');
    $this->effect = EFFECT_STARTOFTURN;
    $this->lastCard = true;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  /**
   * @param Player $player
   */
  public function resolveEffect($player = null)
  {
    for ($bangsLeft = 0; $bangsLeft <= $player->getHp() - 1; $bangsLeft++) {
      $msgActive = $bangsLeft === 0 ?
        clienttranslate('${you} must react to a BANG! from A Fistful Of Cards event, this is the last one') :
        clienttranslate('${you} must react to a BANG! from A Fistful Of Cards event, ${bangsLeft} more to go');
      $msgInactive = $bangsLeft === 0 ?
        clienttranslate('${actplayer} must react to a BANG! from A Fistful Of Cards event, this is the last one') :
        clienttranslate('${actplayer} must react to a BANG! from A Fistful Of Cards event, ${bangsLeft} more to go');
      $atom = Stack::newAtom(ST_REACT, [
        'pId' => $player->getId(),
        'type' => REACT_TYPE_ATTACK,
        'msgActive' => $msgActive,
        'msgInactive' => $msgInactive,
        'bangsLeft' => $bangsLeft,
        'src_name' => $this->name,
        'src' => (new Bang())->jsonSerialize(),
        'missedNeeded' => 1,
      ]);
      Stack::insertOnTop($atom);
    }
  }
}
