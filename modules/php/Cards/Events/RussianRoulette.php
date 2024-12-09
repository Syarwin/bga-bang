<?php
namespace BANG\Cards\Events;
use BANG\Cards\Bang;
use BANG\Core\Stack;
use BANG\Managers\Players;
use BANG\Models\AbstractEventCard;

class RussianRoulette extends AbstractEventCard
{
  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->type = CARD_RUSSIAN_ROULETTE;
    $this->name = clienttranslate('Russian Roulette');
    $this->text = clienttranslate('When Russian Roulette enters play, starting from the Sheriff each player discards a Missed!, until one player does not: he loses 2 life points and the Roulette stops.');
    $this->effect = EFFECT_INSTANT;
    $this->expansion = FISTFUL_OF_CARDS;
  }

  public function resolveEffect($player = null)
  {
    $players = Players::getLivingPlayersStartingWith($player);

    for ($i = 0; $i < 7; $i++) {
      // Why 7? There's 12 Missed! in a game. Let's say we have 4 players, 2 of them are Jourdonnais and somebody with a Barrel
      // If Jourdonnais and Barrel ALWAYS work and other 2 players have 6 Missed each, there will be maximum 6 rounds + 1 just in case
      foreach (array_reverse($players->toArray()) as $player) {
        $msgActive = clienttranslate('${you} must react to a Russian Roulette event with a Missed! or lose 2 life points');
        $msgInactive = clienttranslate('${actplayer} must react to a Russian Roulette event with a Missed! or lose 2 life points');
        $atom = Stack::newAtom(ST_REACT, [
          'pId' => $player->getId(),
          'type' => REACT_TYPE_RUSSIAN_ROULETTE,
          'msgActive' => $msgActive,
          'msgInactive' => $msgInactive,
          'src_name' => $this->name,
          'src' => (new Bang())->jsonSerialize(),
          'missedNeeded' => 1,
          'suspended' => true,
        ]);
        Stack::insertOnTop($atom);
      }
    }
  }
}
