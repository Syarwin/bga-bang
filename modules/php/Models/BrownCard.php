<?php
namespace BANG\Models;
use BANG\Managers\Players;
use BANG\Managers\Cards;
use BANG\Core\Notifications;
use BANG\Managers\Rules;

/*
 * BrownCard: class to handle brown card
 */
class BrownCard extends AbstractCard
{
  public function getColor()
  {
    return BROWN;
  }
  public function isAction()
  {
    return true;
  }

  /**
   * getTargetablePlayers: return the player's id that can be targeted by this card, depending on effect and range
   * @param Player $player
   */
  public function getTargetablePlayers($player)
  {
    $player_ids = [];
    switch ($this->effect['impacts']) {
      case ALL_OTHER:
        $player_ids = Players::getLivingPlayerIdsStartingWith($player, false, $player->getId());
        break;
      case INRANGE:
        $player_ids = $player->getPlayersInRange();
        break;
      case SPECIFIC_RANGE:
        $player_ids = $player->getPlayersInRange($this->effect['range']);
        break;
      case ANY:
        $player_ids = Players::getLivingPlayers()->getIds();
        break;
      case NONE:
        $player_ids = [];
        break;
    }

    // Cannot bang myself
    if ($this->effect['type'] == BASIC_ATTACK) {
      $player_ids = array_values(array_diff($player_ids, [$player->getId()]));
    }

    return $player_ids;
  }

  /*
   * getPlayOptions
   */
  public function getPlayOptions($player)
  {
    $playOptions = [];
    switch ($this->effect['type']) {
      case BASIC_ATTACK:
      case LIFE_POINT_MODIFIER:
        if (in_array($this->effect['impacts'], [NONE, ALL, ALL_OTHER])) {
          return ['target_types' => [TARGET_NONE]];
        }
        break;

      case DRAW:
      case DISCARD:
        $playOptions['targets'] = $this->getTargetablePlayers($player);
        if ($this->effect['impacts'] === NONE) {
          $playOptions['target_types'] = [TARGET_NONE];
        } else {
          $playOptions['target_types'] = [TARGET_CARD];
          $playOptions['status_bar_message'] = clienttranslate('You must choose a card in play or a player\'s hand');
        }
        break;

      case DEFENSIVE:
        return null;
      default:
        return ['target_types' => [TARGET_NONE]];
    }

    return $playOptions;
  }

  /**
   * @param Player $player
   * @param array $args
   * @return void
   */

  public function play($player, $args)
  {
    // Played card always go to the discard
    $this->discard();

    switch ($this->effect['type']) {
      case BASIC_ATTACK:
        $ids = $this->effect['impacts'] == ALL_OTHER ? $player->getOrderedOtherPlayers() : [$args['player']];
        $targetCardId = $args['type'] === LOCATION_INPLAY ? (int) $args['arg'] : null;
        $player->attack($this, $ids, $targetCardId, !!$args['secondCardId']);
        if ($args['secondCardId']) {
          $card = Cards::get($args['secondCardId']);
          if (!in_array($card->getType(), $player->getBangCardTypes())) {
            throw new \BgaVisibleSystemException('Incorrect card type to play with Bang: ' . $card->getType());
          }
          $card->discard();
          Notifications::discardedCard($player, $card);
        }
        break;

      case DRAW:
      case DISCARD:
        // Drawing from deck
        if (!isset($args['type'])) {
          $player->drawCards($this->effect['amount']);
          return null;
        }

        // Drawing/discarding from someone's hand/inplay
        $victim = Players::get($args['player']);
        $card = $args['type'] == 'player' ? $victim->getRandomCardInHand() : Cards::get($args['arg']);
        // TODO: Support Panic yourself more elegantly
        if ($this->effect['type'] == DRAW) {
          Cards::stole($card, $player);
          Notifications::stoleCard($player, $victim, $card, $args['type'] == LOCATION_INPLAY);
        } else {
          $victim->discardCard($card);
        }
        $victim->onChangeHand();
        break;

      case LIFE_POINT_MODIFIER:
        $targets = [];
        if ($this->effect['impacts'] == ALL) {
          $targets = Players::getLivingPlayers();
        } else {
          // TODO: Players::getPlayer() does not exist however code works on production correctly. Investigate and delete this possibility if never used
          $targets[] =
            !isset($args['player']) || is_null($args['player']) ? $player : Players::getPlayer($args['player']);
        }

        foreach ($targets as $target) {
          $target->gainLife($this->effect['amount']);
        }
        break;
    }
  }
}
