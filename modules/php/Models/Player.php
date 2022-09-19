<?php
namespace BANG\Models;
use BANG\Managers\Cards;
use BANG\Managers\Players;
use BANG\Core\Notifications;
use BANG\Core\Log;
use BANG\Core\Stack;
use BANG\Core\Globals;
use bang;

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
  protected $score;
  protected $generalStore;

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
      $this->score = (int) $row['player_score'];
      $this->generalStore = (int) $row['player_autopick_general_store'];
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
  public function getCharacter()
  {
    return $this->character;
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
  public function isAutoPickGeneralStore()
  {
    return $this->generalStore == GENERAL_STORE_AUTO_PICK;
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
      'score' => $this->score,
      'powers' => $this->text,
      'hp' => $this->hp,
      'bullets' => $this->bullets,
      'hand' => $current ? $this->getHand($this->id)->toArray() : [],
      'handCount' => $this->countHand($this->id),
      'role' => $current || $this->role == SHERIFF || $this->eliminated || Players::isEndOfGame() ? $this->role : null,
      'inPlay' => $this->getCardsInPlay()->toArray(),

      'preferences' => $current
        ? [
          OPTION_GENERAL_STORE_LAST_CARD => $this->generalStore,
        ]
        : [],
    ];
  }

  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'characterId' => $this->character,
    ];
  }

  /**
   * saves eliminated status and hp to the database
   */
  public function save()
  {
    self::DbQuery("UPDATE player SET `player_hp` = {$this->hp} WHERE `player_id` = {$this->id}");
  }

  /*************************
   ********** Utils *********
   *************************/

  /*
   * Draw $amount card from deck and notify them
   */
  public function drawCards($amount)
  {
    if ($amount > 0) {
      $cards = Cards::deal($this->id, $amount);
      Notifications::drawCards($this, $cards);
      $this->onChangeHand();
    }
  }

  /*
   * Discard a card and notify (with/without a message) it
   */
  public function discardCard($card, $silent = false)
  {
    $card->discard();
    Notifications::discardedCard($this, $card, $silent);
    $this->onChangeHand();
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
   * Add atom to Stack to flip a card in the next state
   */
  public function addFlipAtom($src)
  {
    $atom = Stack::newAtom(ST_FLIP_CARD, [
      'pId' => $this->id,
      'src' => $src->jsonSerialize(),
    ]);
    $topAtom = Stack::top();
    if (array_key_exists('missedNeeded', $topAtom)) {
      $atom['missedNeeded'] = $topAtom['missedNeeded'];
    }
    Stack::insertOnTop($atom);
  }

  /*
   * Flip a card, notify and return the card
   */
  public function flip($src)
  {
    $cards = Cards::drawForLocation(LOCATION_FLIPPED, 1);
    $flipped = $cards->first();
    Notifications::flipCard($this, $flipped, $src);
    $this->addResolveFlippedAtom($src);
  }

  public function addResolveFlippedAtom($src)
  {
    $atom = Stack::newAtom(ST_RESOLVE_FLIPPED, [
      'pId' => $this->id,
      'src' => $src->jsonSerialize(),
    ]);
    $topAtom = Stack::top();
    if (array_key_exists('missedNeeded', $topAtom)) {
      $atom['missedNeeded'] = $topAtom['missedNeeded'];
    }
    Stack::insertOnTop($atom);
  }

  /**
   * incresase the life points of a player.
   */
  public function gainLife($amount = 1)
  {
    if ($this->hp == $this->bullets) {
      // TODO : add notification ?
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
  public function loseLife($amount = 1)
  {
    $this->hp -= $amount;
    $this->save();
    Notifications::lostLife($this, $amount);
    if ($this->hp <= 0) {
      $ctx = Stack::getCtx();
      $isDuel = Players::getLivingPlayers()->count() <= 2;
      $beersInHand = $this->getHand()
        ->filter(function ($card) {
          return $card->getType() == CARD_BEER;
        })
        ->count();
      $isKetchumAndCanUseAbility = $this->character == SID_KETCHUM && $this->getHand()->count() >= 2;
      $canDrinkBeerToLive = (!$isDuel && $beersInHand > 0) || $isKetchumAndCanUseAbility;
      $nextState = $canDrinkBeerToLive ? ST_REACT_BEER : ST_PRE_ELIMINATE_DISCARD;
      $atomType = $canDrinkBeerToLive ? 'beer' : 'eliminate';
      $atom = Stack::newAtom($nextState, [
        'type' => $atomType,
        'src' => $ctx['src'] ?? null,
        'attacker' => $ctx['attacker'] ?? null,
        'pId' => $this->id,
      ]);
      Stack::insertAfterCardResolution($atom);
    }
  }

  /**
   * used when player drinks a beer or Sid Ketchum uses his ability to gain life discarding 2 cards
   */
  public function eliminateIfOutOfHp()
  {
    // If it's not enough, add a ELIMINATE node
    if ($this->getHp() <= 0) {
      $ctx = Stack::getCtx();
      $atom = Stack::newAtom(ST_PRE_ELIMINATE_DISCARD, [
        'type' => 'eliminateDiscard',
        'src' => $ctx['src'],
        'attacker' => $ctx['attacker'],
        'pId' => $this->getId(),
      ]);
      Stack::insertAfterCardResolution($atom);
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
  public function getRandomCardInHand($raiseException = true)
  {
    $cards = self::getHand()->toArray();
    if (empty($cards)) {
      if ($raiseException) {
        throw new \BgaVisibleSystemException('Cannot draw a card in an empty hand');
      } else {
        return null;
      }
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
      if (($card->getEffect()['type'] ?? null) == RANGE_DECREASE) {
        $dist--;
      }
    }
    foreach ($this->getCardsInPlay() as $card) {
      if (($card->getEffect()['type'] ?? null) == RANGE_INCREASE) {
        $dist++;
      }
    }
    return $dist;
  }

  public function isInRange($enemy, $range)
  {
    return $enemy->getDistanceTo($this) <= $range;
  }

  public function getDistances()
  {
    $dist = [];
    foreach (Players::getLivingPlayers() as $pId => $player2) {
      $dist[$pId] = $player2->getDistanceTo($this);
    }
    return $dist;
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
    $cards = $this->getHand()
      ->filter(function ($card) {
        return $card->getType() == CARD_BANG;
      })
      ->map(function ($card) {
        return [
          'id' => $card->getId(),
          'options' => ['target_type' => TARGET_NONE],
          'amount' => 1,
        ];
      })
      ->toArray();

    return [
      'cards' => $cards,
      'character' => null,
    ];
  }

  /*
   * return the list of beer cards
   */
  public function getBeerCards()
  {
    return $this->getHand()->filter(function ($card) {
      return $card->getType() == CARD_BEER;
    });
  }

  /*
   * Return the list of beer option for reacting when dying
   * Overwritten by Sid Ketchum
   */
  public function getBeerOptions()
  {
    return [
      'cards' => $this->getBeerCards()->toArray(),
    ];
  }

  /*
   * return defensive options
   */
  public function getDefensiveOptions()
  {
    $missedNeeded = Stack::top()['missedNeeded'] ?? 1;

    // Defensive cards in hand
    $res = $this->getHand()
      ->filter(function ($card) {
        return $card->getColor() == BROWN && $card->getEffectType() == DEFENSIVE;
      })
      ->map(function ($card) use ($missedNeeded) {
        return [
          'id' => $card->getId(),
          'amount' => $missedNeeded,
          'options' => ['target_type' => TARGET_NONE],
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
        'options' => ['target_type' => TARGET_NONE],
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
      if ($card->getType() == $targetCard->getType()) {
        return true;
      }
    }
    return false;
  }

  /***************************************
   ****************************************
   **************** Actions ***************
   ****************************************
   ***************************************/

  /**
   * startOfTurn: is called at the beginning of each turn (before the drawing phase)
   */
  public function startOfTurn()
  {
    $equipment = $this->getCardsInPlay()->toArray();

    // Sort cards to make sure dynamite gets handled before jail
    usort($equipment, function ($a, $b) {
      return $a->getType() > $b->getType();
    });

    foreach ($equipment as $card) {
      $card->startOfTurn($this);
    }
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
          'type' => $card->getType(),
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
    Notifications::updateDistances();
    $this->onChangeHand();
  }

  /**
   * attack : performs an attack on all given players
   */
  public function attack($card, $playerIds)
  {
    $atom = $this->getReactAtomForAttack($card);
    foreach (array_reverse($playerIds) as $pId) {
      $atom['pId'] = $pId;
      Stack::insertOnTop($atom);
    }
  }

  public function getReactAtomForAttack($card)
  {
    $src = $card->getName();
    if ($this->character == CALAMITY_JANET && $card->getType() == CARD_MISSED) {
      $src = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    return Stack::newAtom(ST_REACT, [
      'type' => 'attack',
      'msgActive' => clienttranslate('${you} may react to ${src_name}'),
      'msgWaiting' => clienttranslate('${actplayer} has to react to ${src_name}. You may already select your reaction'),
      'msgInactive' => clienttranslate('${actplayer} may react to ${src_name}'),
      'src_name' => $src,
      'src' => $card->jsonSerialize(),
      'attacker' => $this->id,
      'missedNeeded' => 1,
    ]);
  }

  /**
   * react: whenever a player react by passing or playing a card
   */
  public function react($ids)
  {
    $ctx = Stack::getCtx();
    // If characterId is set, the player was reacting to its ability, not to a card (eg Kit Carlson)
    if (isset($ctx['src']['characterId'])) {
      $this->useAbility($ids);
      return;
    }

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
        $this->onChangeHand();
        $this->notifyAboutAnotherMissed();
      }
    }
  }

  protected function notifyAboutAnotherMissed()
  {
    $nextAtom = Stack::getNextState();
    $nextAtomIsAttack = isset($nextAtom['type']) && $nextAtom['type'] == 'attack';
    $nextAtomMissedNeeded = isset($nextAtom['missedNeeded']) ? $nextAtom['missedNeeded'] : -1;
    $topAtom = Stack::top();
    $topAtomMissedNeeded = isset($topAtom['missedNeeded']) ? $topAtom['missedNeeded'] : -1;
    if ($nextAtomIsAttack && $nextAtomMissedNeeded == 1 && $topAtomMissedNeeded == 2) {
      Notifications::tell(clienttranslate('But ${player_name} needs another Missed!'), [
        'player_name' => $this->getName(),
      ]);
    }
  }

  /**
   *
   */
  public function prepareSelection($source, $playerIds, $isPrivate, $amountToPick, $toResolveFlipped = false)
  {
    $src = $source instanceof \BANG\Models\Player ? $source->getCharName() : $source->getName();
    $atom = Stack::newAtom(ST_SELECT_CARD, [
      'src_name' => $src,
      'amountToPick' => $amountToPick,
      'isPrivate' => $isPrivate,
      'toResolveFlipped' => $toResolveFlipped,
      'src' => $source->jsonSerialize(),
    ]);

    foreach (array_reverse($playerIds) as $pId) {
      $atom['pId'] = $pId;
      Stack::insertOnTop($atom);
    }
  }

  /**
   * Eliminate a player
   */
  public function eliminate()
  {
    $ctx = Stack::getCtx();

    // get player who eliminated this player
    $byPlayer = null;
    if (array_key_exists('attacker', $ctx) && $ctx['attacker'] != $this->id) {
      $byPlayer = Players::get($ctx['attacker']);
    }

    // Let characters react => mostly Vulture
    foreach (Players::getLivingPlayers($this->id) as $player) {
      $player->onPlayerEliminated($this);
    }

    // Discard cards
    $this->discardAllCards();
    // Eliminate player
    bang::get()->eliminatePlayer($this->id);
    $this->eliminated = true;
    $this->save();

    // Check if game should end
    if (Stack::isItLastElimination() && Players::isEndOfGame()) {
      bang::get()->setWinners();
    }

    Notifications::playerEliminated($this);

    //handle rewards/penalties
    if ($byPlayer != null) {
      if ($this->getRole() == OUTLAW) {
        $byPlayer->drawCards(3);
      }
      if ($this->getRole() == DEPUTY && $byPlayer->getRole() == SHERIFF) {
        Notifications::tell(clienttranslate('The Sheriff eliminated his Deputy and must discard all cards'), []);
        return $byPlayer->getId();
      }
    }

    // Remove all related nodes that could still be there (reactions/powers)
    Stack::removePlayerAtoms($this->id);
    return true;
  }

  /**
   * Happens when dead or Sheriff killed one of its deputy
   */
  public function discardAllCards()
  {
    $hand = $this->getHand();
    $equipment = $this->getCardsInPlay();
    $hand->merge($equipment)->map(function ($card) {
      Cards::discard($card);
    });
    Notifications::discardedCards($this, $equipment, true, $equipment->getIds());
    Notifications::discardedCards($this, $hand, false, $hand->getIds());
    $this->onChangeHand();
  }

  /**
   * called whenever a player is eliminated
   * atm just for Vulture Sam
   */
  public function onPlayerEliminated($player)
  {
  }

  public function onPlayerPreEliminated($player)
  {
  }

  /**
   * called whenever the hand of player change
   * atm just for Suzy
   */
  public function onChangeHand()
  {
    Notifications::updateHand($this);
    $this->checkHand();
  }

  public function checkHand()
  {
  }

  public function setGeneralStorePref($value)
  {
    self::DB()->update(['player_autopick_general_store' => $value], $this->id);
  }
}
