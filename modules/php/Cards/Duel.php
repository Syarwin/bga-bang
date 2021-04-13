<?php
namespace BANG\Cards;
use BANG\Managers\Players;
use BANG\Core\Stack;

class Duel extends \BANG\Models\BrownCard
{
  public function __construct($id = null, $copy = '')
  {
    parent::__construct($id, $copy);
    $this->type = CARD_DUEL;
    $this->name = clienttranslate('Duel');
    $this->text = clienttranslate(
      'A target player discards a BANG! then you, etc. First player failing to discard a BANG! loses 1 life point.'
    );
    $this->symbols = [[$this->text]];
    $this->copies = [
      BASE_GAME => ['QD', 'JS', '8C'],
      DODGE_CITY => [],
    ];
    $this->effect = [
      'type' => OTHER,
      'range' => 0,
      'impacts' => ANY,
    ];
  }

  /*
   *
   */
  public function getPlayOptions($player)
  {
    $livings = Players::getLivingPlayers($player->getId());
    return [
      'type' => OPTION_PLAYER,
      'targets' => $livings->getIds(),
    ];
  }

  public function play($player, $args)
  {
    parent::play($player, $args);
    $atom = [
      'state' => ST_REACT,
      'type' => 'duel',
      'msgActive' => clienttranslate('${you} may react to the duel by discarding a Bang!'),
      'msgInactive' => clienttranslate('${actplayer} may react to the duel by discarding a Bang!'),
      'src' => $this->jsonSerialize(),
      'attacker' => $player->getId(),
      'opponent' => $args['player'],
      'pId' => $args['player'],
    ];

    Stack::insertOnTop($atom);
  }

  public function getReactionOptions($player)
  {
    return $player->getBangCards();
  }

  public function pass($player)
  {
    $player->loseLife();
  }

  public function react($card, $player)
  {
    $player->discardCard($card);

    // Get top of the stack, change pId and insertAfter
    $atom = Stack::top();
    $atom['pId'] = $atom['pId'] == $atom['attacker'] ? $atom['opponent'] : $atom['attacker'];
    Stack::insertAfter($atom);
  }
}
