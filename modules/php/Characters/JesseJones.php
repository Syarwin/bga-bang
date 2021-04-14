<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Helpers\Utils;
use BANG\Managers\Cards;
use BANG\Managers\Players;

class JesseJones extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = JESSE_JONES;
    $this->character_name = clienttranslate('Jesse Jones');
    $this->text = [
      clienttranslate(
        'During phase 1 of his turn, he may choose to draw the first card from the deck, or randomly from the hand of any other player.'
      ),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function drawCardsPhaseOne()
  {
    // TODO : auto skip if argDrawCard only has 'deck' inside
    Stack::insertAfter([
      'state' => ST_ACTIVE_DRAW_CARD,
      'pId' => $this->id,
    ]);
  }

  public function argDrawCard()
  {
    $options = Players::getLivingPlayers($this->id)
      ->filter(function ($player) {
        return $player->getHand()->count() > 0;
      })
      ->getIds();
    $options[] = 'deck';
    return ['options' => $options];
  }

  public function useAbility($args)
  {
    if ($args['selected'] == 'deck') {
      $this->drawCards(2);
    } else {
      // TODO : add sanity check

      // Stole the first card
      $victim = Players::get($args['selected']);
      $card = $victim->getRandomCardInHand();
      Cards::move($card->getId(), LOCATION_HAND, $this->id);
      Notifications::stoleCard($this, $victim, $card, false);

      // Deal the second one
      $this->drawCards(1);
    }
  }
}
