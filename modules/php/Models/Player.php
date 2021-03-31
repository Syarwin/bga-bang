<?php
namespace BANG\Models;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Helpers\Utils;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;

/*
 * Player: all utility functions concerning a player
 */
class Player extends \BANG\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';

  protected $id;
  protected $no; // natural order
  protected $name; // player name
  protected $color;
  protected $eliminated = false;
  protected $hp;
  protected $zombie = false;
  protected $role;

  // --character properties
  protected $character; //the int-constant
  protected $character_name;
  protected $text;
  protected $bullets;
  protected $expansion = BASE_GAME;

  public function __construct($row)
  {
    if ($row != null) {
      $this->id = (int) $row['player_id'];
      $this->no = (int) $row['player_no'];
      $this->name = $row['player_name'];
      $this->color = $row['player_color'];
      $this->eliminated = $row['player_eliminated'] == 1;
      $this->hp = (int) $row['player_hp'];
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = $row['player_role'];
      $this->bullets = (int) $row['player_bullets'];
    }
  }

  /*
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }
  public function getNo()
  {
    return $this->no;
  }
  public function getName()
  {
    return $this->name;
  }
  public function getColor()
  {
    return $this->color;
  }
  public function getHp()
  {
    return $this->hp;
  }
  public function getRole()
  {
    return $this->role;
  }
  public function getCharName()
  {
    return $this->character_name;
  }
  public function isEliminated()
  {
    return $this->eliminated;
  }
  public function isZombie()
  {
    return $this->zombie;
  }
  public function isAvailable($expansions)
  {
    return in_array($this->expansion, $expansions);
  }

  public function getPosition()
  {
    return Players::getPlayerPositions()[$this->id];
  }
  public function getText()
  {
    return $this->text;
  }
  public function getExpansion()
  {
    return $this->expansion;
  }
  public function getBullets()
  {
    return $this->bullets;
  }

  public function getHand()
  {
    return Cards::getHand($this->id);
  }
  public function getCardsInPlay()
  {
    return Cards::getInPlay($this->id);
  }
  public function countHand()
  {
    return Cards::countHand($this->id);
  }
  public function setHp($hp)
  {
    $this->hp = $hp;
  }

  public function getUiData($currentPlayerId = null)
  {
    $current = $this->id == $currentPlayerId;
    return [
      'id' => (int) $this->id,
      'eliminated' => (int) $this->eliminated,
      'no' => (int) $this->no,
      'name' => $this->getName(),
      'color' => $this->color,
      'characterId' => $this->character,
      'character' => $this->character_name,
      'powers' => $this->text,
      'hp' => $this->hp,
      'bullets' => $this->bullets,
      'hand' => $current ? $this->getHand($this->id)->toArray() : $this->countHand($this->id),
      'role' => $current || $this->role == SHERIFF || $this->eliminated || Players::isEndOfGame() ? $this->role : null,
      'inPlay' => $this->getCardsInPlay()->toArray(),
    ];
  }

  /**
   * saves eliminated status and hp to the database
   */
  public function save()
  {
    $eliminated = $this->eliminated ? 1 : 0;
    self::DbQuery(
      "UPDATE player SET `player_eliminated` = $eliminated, `player_hp` = {$this->hp} WHERE `player_id` = {$this->id}"
    );
  }

  /*************************
   ********** Utils *********
   *************************/

  /*
   * Draw $amount card from deck and notify them
   */
  public function drawCards($amount)
  {
    $cards = Cards::deal($this->id, $amount);
    Notifications::drawCards($this, $cards);
  }

  /*
   * Discard a card and notify (with/without a message) it
   */
  public function discardCard($card, $silent = false)
  {
    $card->discard();
    Notifications::discardedCard($this, $card, $silent);
    // TODO $this->onCardsLost();
  }

  /*
   * Discard its weapon
   */
  public function discardWeapon()
  {
    $weapon = $this->getWeapon();
    if (!is_null($weapon)) {
      $this->discardCard($weapon, true);
    }
  }

  /*
   * Draw! (careful, not the same as drawCard), notify and return the card
   */
  public function flip($args, $src)
  {
    $card = Cards::draw();
    Notifications::flipCard($this, $card, $src);
    return $card;
  }

  /**
   * incresase the life points of a player.
   */
  public function gainLife($amount = 1)
  {
    if ($this->hp == $this->bullets) {
      return;
    }

    $this->hp += $amount;
    if ($this->hp > $this->bullets) {
      $this->hp = $this->bullets;
    }
    $this->save();
    Notifications::gainedLife($this, $amount);
  }

  /**
   * reduces the life points of a player by 1.
   * return: whether the player was eliminated
   */
  public function looseLife($amount = 1)
  {
    $this->hp -= $amount;
    $this->save();
    Notifications::lostLife($this, $amount);
    if ($this->hp <= 0) {
      // TODO : add something in the stack after current atomic resolution
//      Log::addAction('lastState', [Utils::getStateName()]);
    }
  }

  /************************************
   ********** Advanced getters *********
   ************************************/

  /*
   * Return the set of all other living players
   */
  public function getOrderedOtherPlayers()
  {
    return Players::getLivingPlayersStartingWith($this, [$this->id]);
  }

  /*
   * Return a random card from the hand (useful for drawing in hand for instance)
   */
  public function getRandomCardInHand()
  {
    $cards = self::getHand()->toArray();
    if (empty($cards)) {
      throw new \BgaVisibleSystemException('Cannot draw a card in an empty hand');
    }
    shuffle($cards);
    return $cards[0];
  }

  /**
   * returns the current distance to an enmy from the view of the enemy
   * should not be called on the player checking for targets but on the other players
   */
  public function getDistanceTo($enemy)
  {
    $positions = Players::getPlayerPositions();
    $pos1 = $positions[$this->getId()];
    $pos2 = $positions[$enemy->getId()];
    $d = abs($pos2 - $pos1);
    $dist = min($d, count($positions) - $d);
    foreach ($enemy->getCardsInPlay() as $card) {
      if ($card->getEffect()['type'] == RANGE_DECREASE) {
        $dist--;
      }
    }
    foreach ($this->getCardsInPlay() as $card) {
      if ($card->getEffect()['type'] == RANGE_INCREASE) {
        $dist++;
      }
    }
    return $dist;
  }

  public function isInRange($enemy, $range)
  {
    return $enemy->getDistanceTo($this) <= $range;
  }

  /**
   * getPlayerInRange : Returns the players ids in range of weapon
   */
  public function getPlayersInRange($range = null)
  {
    $range = $range ?? $this->getRange();
    return Players::getLivingPlayers()
      ->filter(function ($player) use ($range) {
        return $this->isInRange($player, $range); //($player->getDistanceTo($this) <= $range); // TODO : use isInRange => weird bug...
      })
      ->getIds();
  }

  /**
   * getWeapon : Returns weapon card of player, or null if not equipped
   */
  public function getWeapon()
  {
    return $this->getCardsInPlay()->reduce(function ($weapon, $card) {
      return $card->isWeapon() ? $card : $weapon;
    }, null);
  }

  /**
   * getRange : Returns the range of player's weapon
   */
  public function getRange()
  {
    $weapon = $this->getWeapon();
    return is_null($weapon) ? 1 : $weapon->getEffect()['range'];
  }

  public function hasUnlimitedBangs()
  {
    $weapon = $this->getWeapon();
    return !is_null($weapon) && $weapon->getType() == CARD_VOLCANIC;
  }

  public function hasPlayedBang()
  {
    return !is_null(Log::getLastAction('bangPlayed', $this->id));
  }

  /*
   * return the list of bang cards (for indians and duel for instance)
   */
  public function getBangCards()
  {
    $cards = $this->getCardsInHand()
      ->filter(function ($card) {
        return $card->getType() == CARD_BANG;
      })
      ->map(function ($card) {
        return [
          'id' => $card->getId(),
          'options' => ['type' => OPTION_NONE],
          'amount' => 1,
        ];
      });

    return [
      'cards' => $cards,
      'character' => null,
    ];
  }

  /*
   * return defensive options
   */
  public function getDefensiveOptions()
  {
    $args = Log::getLastAction('cardPlayed');
    $amount = 1; // TODO isset($args['missedNeeded']) ? $args['missedNeeded'] : 1;

    // Defensive cards in hand
    $res = $this->getHand()
      ->filter(function ($card) {
        return $card->getColor() == BROWN && $card->getEffectType() == DEFENSIVE;
      })
      ->map(function ($card) use ($amount) {
        return [
          'id' => $card->getId(),
          'amount' => $amount,
          'options' => ['type' => OPTION_NONE],
        ];
      })
      ->toArray();

    // Defensive cards in play
    $card = $this->getCardsInPlay()->reduce(function ($barrel, $card) {
      return $card->getType() == CARD_BARREL && !$card->wasPlayed() ? $card : $barrel;
    }, null);
    if (!is_null($card)) {
      $res[] = [
        'id' => $card->getId(),
        'amount' => 1,
        'options' => ['type' => OPTION_NONE],
      ];
    }

    return [
      'cards' => array_values($res),
      'character' => null,
    ];
  }

  public function hasCardCopyInPlay($targetCard)
  {
    $equipment = $this->getCardsInPlay();
    foreach ($equipment as $card) {
      if ($card->type == $targetCard->type) {
        return false;
      }
    }
    return true;
  }

  /***************************************
   ****************************************
   **************** Actions ***************
   ****************************************
   ***************************************/

  /**
   * startOfTurn: is called at the beggining of each turn (before the drawing phase)
   *   return: the new state it should continue with
   */
  public function startOfTurn()
  {
    return 'draw'; // TODO

    $equipment = $this->getCardsInPlay();
    // Sort cards to make sure dynamite gets handled before jail
    Utils::sort($equipment, function ($a, $b) {
      return $a->getType() > $b->getType();
    });

    return array_reduce(
      $equipment,
      function ($state, $card) {
        if ($state == 'skip' || $card->getEffectType() != STARTOFTURN) {
          return $state;
        }
        $newState = $card->activate($this);
        return $newState ?: $state;
      },
      'draw'
    );
  }

  /**
   * getHandOptions: give the list of playable cards in hand, along with their options
   */
  public function getHandOptions()
  {
    $options = $this->getHand()
      ->map(function ($card) {
        return [
          'id' => $card->getId(),
          'options' => $card->getPlayOptions($this),
        ];
      })
      ->filter(function ($card) {
        return !is_null($card['options']);
      });

    return [
      'cards' => $options->toArray(),
      'character' => null,
    ];
  }

  /**
   * playCard: play a card given by id with args to specify the chosen option
   */
  public function playCard($card, $args)
  {
    Notifications::cardPlayed($this, $card, $args);
    Log::addCardPlayed($this, $card, $args);
    $card->play($this, $args);
    // TODO $this->onCardsLost();
  }

  /**
   * attack : performs an attack on all given players
   */
  public function attack($card, $playerIds)
  {
    $src = $card->getName();
    if ($this->character == CALAMITY_JANET && $card->getType() == CARD_MISSED) {
      $src = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    $atom = [
      'state' => ST_REACT,
      'type' => 'attack',
      'msgActive' => clienttranslate('${you} may react to ${src_name}'),
      'msgWaiting' => clienttranslate('${actplayer} has to react to ${src_name}. You may already select your reaction'),
      'msgInactive' => clienttranslate('${actplayer} may react to ${src_name}'),
      'src_name' => $src,
      'src' => $card->jsonSerialize(),
      'attacker' => $this->id,
      'selection' => [],
    ];

    foreach (array_reverse($playerIds) as $pId) {
      $atom['pId'] = $pId;
      Stack::insertOnTop($atom);
    }
  }

  /**
   * react: whenever a player react by passing or playing a card
   */
  public function react($ids, $ctx)
  {
    $card = Cards::get($ctx['src']['id']);
    if (is_null($ids)) {
      // PASS
      return $card->pass($this);
    } else {
      if (!is_array($ids)) {
        $ids = [$ids];
      }

      foreach ($ids as $id) {
        $reactionCard = Cards::get($id);
        $card->react($reactionCard, $this);
        // TODO $this->onCardsLost();
      }
    }
  }
}
