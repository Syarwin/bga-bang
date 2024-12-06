<?php
namespace BANG\Models;
use BANG\Core\Globals;
use BANG\Helpers\Collection;
use BANG\Helpers\GameOptions;
use BANG\Managers\Cards;
use BANG\Managers\EventCards;
use BANG\Managers\Players;
use BANG\Core\Notifications;
use BANG\Core\Stack;
use BANG\Managers\Rules;
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
  protected $altCharacter;
  protected $character_name;
  protected $text;
  protected $bullets;
  protected $expansion = BASE_GAME;
  protected $characterChosen;
  // see constants for player's living status from constants.inc.php
  protected $livingStatus;
  protected $agreedToDisclaimer;

  public function __construct($row)
  {
    if ($row != null) {
      $this->characterChosen = !(array_key_exists('player_character_chosen', $row) && (int)$row['player_character_chosen'] === 0);
      $this->id = (int)$row['player_id'];
      $this->no = (int)$row['player_no'];
      $this->name = $row['player_name'];
      $this->color = $row['player_color'];
      $this->eliminated = $row['player_eliminated'] == 1;
      $this->hp = $this->characterChosen ? (int)$row['player_hp'] : null;
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = (int)$row['player_role'];
      $this->bullets = $this->characterChosen ? (int)$row['player_bullets'] : null;
      $this->score = (int)$row['player_score'];
      $this->generalStore = (int)$row['player_autopick_general_store'];
      $this->character = (int)$row['player_character'];
      $this->altCharacter = (int) $row['player_alt_character'];
      $this->livingStatus = (int) $row['player_unconscious'];
      $this->agreedToDisclaimer = isset($row['player_agreed_to_disclaimer']) ? (int) $row['player_agreed_to_disclaimer'] === 1 : null;
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

  /**
   * @return AbstractCard
   */
  public function getLastCardFromHand()
  {
    return $this->getHand()->last();
  }

  /**
   * @return Collection
   */
  public function getCardsInPlay()
  {
    return Cards::getInPlay($this->id);
  }

  /**
   * @return Collection
   */
  public function getBlueCardsInPlay()
  {
    return $this->getCardsInPlay()->filter(function ($card) {
      return $card->getColor() === BLUE;
    });
  }

  /**
   * @return array
   */
  public function getMissedWithOptions()
  {
    $allMissed = $this->getHand()->filter(function ($card) {
      return $card->getType() === CARD_MISSED;
    });
    return $this->addOptionsTo($allMissed, false);
  }

  public function countHand()
  {
    return Cards::countHand($this->id);
  }

  public function isAutoPickGeneralStore()
  {
    return $this->generalStore == GENERAL_STORE_AUTO_PICK;
  }

  public function isCharacterChosen()
  {
    return $this->characterChosen;
  }
  /**
   * @return boolean
   */
  public function isUnconscious()
  {
    return $this->livingStatus === DEAD_GHOST;
  }

  /**
   * @return boolean|null
   */
  public function isAgreedToDisclaimer()
  {
    return $this->agreedToDisclaimer;
  }

  public function getUiData($currentPlayerId = null)
  {
    $current = $this->id == $currentPlayerId;
    return [
      'id' => $this->id,
      'eliminated' => (int) $this->eliminated,
      'unconscious' => $this->livingStatus === DEAD_GHOST,
      'no' => $this->no,
      'name' => $this->getName(),
      'color' => $this->color,
      'characterId' => $this->character,
      'character' => $this->character_name,
      'score' => $this->score,
      'powers' => $this->text,
      'hp' => $this->hp,
      'bullets' => $this->bullets,
      'hand' => $current ? $this->getHand()->toArray() : [],
      'handCount' => $this->countHand(),
      'role' => $current || $this->role == SHERIFF || $this->eliminated || $this->livingStatus !== FULLY_ALIVE || Players::isEndOfGame() ? $this->role : null,
      'inPlay' => $this->getCardsInPlay()->toArray(),

      'preferences' => $current
        ? [
          OPTION_GENERAL_STORE_LAST_CARD => $this->generalStore,
        ]
        : [],
    ];
  }

  /**
   * Returns data specific to a character
   * @return array
   */
  public function getUiCharacterSpecificData()
  {
    return [
      'characterId' => $this->character,
      'character' => $this->character_name,
      'powers' => $this->text,
      'bullets' => $this->bullets,
      'hp' => $this->bullets,
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
   * @param bool $eliminate
   */
  public function save($eliminate = false)
  {
    $unconsciousStatus = $eliminate ? ', `player_unconscious` = 1' : '';
    $newHP = $eliminate && $this->hp < 0 ? 0 : $this->hp;
    self::DbQuery("UPDATE player SET `player_hp` = {$newHP}{$unconsciousStatus} WHERE `player_id` = {$this->id}");
  }

  /*************************
   ********** Utils *********
   *************************/

  /**
   * Draw $amount card from deck and notify them
   * @param int $amount
   * @return Collection|null
   */
  public function drawCards($amount, $publicly = false)
  {
    if ($amount > 0) {
      $location = Rules::getDrawOrDiscardCardsLocation(LOCATION_DECK);
      $cards = Cards::deal($this->id, $amount, $location);
      if ($cards->count() > 0) {
        Notifications::drawCards($this, $cards, $location === LOCATION_DISCARD || $publicly, $location);
      }
      if ($location === LOCATION_DISCARD && $cards->count() !== $amount) {
        Notifications::showMessageToAll(
          clienttranslate('The discard was empty while drawing so ${player_name} drew remaining cards from the deck'),
          [ 'player' => $this ],
        false
        );
        $cards = Cards::deal($this->id, $amount - $cards->count());
        Notifications::drawCards($this, $cards);
      }
      $this->onChangeHand();
    }
    return $cards ?? null;
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
    if ($this->hp > 0) {
      $this->hp -= $amount;
      $this->save();
      Notifications::lostLife($this, $amount);
    }
    $this->addRevivalAtomOrEliminate();
  }

  /**
   * used when player drinks a beer or Sid Ketchum uses his ability to gain life discarding 2 cards
   */
  public function addRevivalAtomOrEliminate()
  {
    if ($this->hp <= 0) {
      $isDuel = Players::getLivingPlayers()->count() <= 2;
      $beersInHand = $this->getHand()
        ->filter(function ($card) {
          return $card->getType() == CARD_BEER;
        })
        ->count();
      $isKetchumAndCanUseAbility = $this->character == SID_KETCHUM && $this->getHand()->count() >= 2 && Rules::isAbilityAvailable();
      $canDrinkBeerToLive = (!$isDuel && $beersInHand > 0) || $isKetchumAndCanUseAbility;
      if ($beersInHand > 0 && !Rules::isBeerAvailable() && !$isKetchumAndCanUseAbility) {
        $canDrinkBeerToLive = false;
        // Assuming The Reverend is the only reason of beer unavailability status for now. This might change in future
        $msg = clienttranslate('Even though ${player_name} had beers while dying, they could not be played because of The Reverend event card');
        Notifications::tell($msg, ['player' => $this]);
      }
      $nextState = $canDrinkBeerToLive ? ST_REACT_BEER : ST_PRE_ELIMINATE_DISCARD;
      $atomType = $canDrinkBeerToLive ? 'beer' : 'eliminate';

      $eliminationAlreadyInStack = Stack::getFirstIndex([
        'type' => 'eliminate',
        'pId' => $this->getId(),
        'forceEliminate' => true,
      ]) !== -1;
      if (!$eliminationAlreadyInStack) { // This is when a ghost is dying during Ghost Town, no need to die twice
        $this->addAtomAfterCardResolution($nextState, $atomType);
      }
    }
  }

  /**
   * @param int $nextState
   * We expect $type to be either 'beer' or 'eliminate' so we probably need enum here
   * @param string $type
   */
  public function addAtomAfterCardResolution($nextState, $type)
  {
    $ctx = Stack::getCtx();
    $atom = Stack::newAtom($nextState, [
      'type' => $type,
      'src' => $ctx['src'] ?? null,
      'attacker' => $ctx['attacker'] ?? null,
      'pId' => $this->id,
    ]);
    Stack::insertAfterCardResolution($atom, false);
  }

  /************************************
   ********** Advanced getters *********
   ************************************/

  /*
   * Return the set of all other living players
   */
  public function getOrderedOtherPlayers()
  {
    return Players::getLivingPlayerIdsStartingWith($this, false, $this->id);
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
   * returns the current distance to an enemy from the view of the enemy
   * should not be called on the player checking for targets but on the other players
   * @return int
   */
  public function getDistanceTo($enemy)
  {
    if (Rules::isDistanceForcedToOne()) {
      $dist = 1;
    } else {
      $positions = Players::getPlayerPositions();
      $pos1 = $positions[$this->getId()];
      $pos2 = $positions[$enemy->getId()];
      $d = abs($pos2 - $pos1);
      $dist = min($d, count($positions) - $d);
    }
    if (!Rules::isIgnoreCardsInPlay()) {
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
    }
    return $dist;
  }

  public function isInRange($enemy, $range)
  {
    return $enemy->getDistanceTo($this) <= $range;
  }

  /**
   * @return int[]
   */
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
    return is_null($weapon) || Rules::isIgnoreCardsInPlay() ? 1 : $weapon->getEffect()['range'];
  }

  public function hasUnlimitedBangs()
  {
    $weapon = $this->getWeapon();
    return !Rules::isIgnoreCardsInPlay() && !is_null($weapon) && $weapon->getType() === CARD_VOLCANIC;
  }

  /*
   * return the list of bang cards (for indians and duel for instance)
   */
  public function getBangCards($options = [])
  {
    if (empty($options)) {
      $options = ['target_types' => [TARGET_NONE]];
    }
    $cards = $this->getHand()
      ->filter(function ($card) {
        return $card->getType() == CARD_BANG;
      })
      ->map(function ($card) use ($options) {
        return [
          'id' => $card->getId(),
          'options' => $options,
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
    $beerCards = Rules::isBeerAvailable() ? $this->getBeerCards()->toArray() : [];
    return [
      'cards' => $beerCards,
    ];
  }

  /**
   * Returns defensive options
   * @return array
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
          'options' => ['target_types' => [TARGET_NONE]],
        ];
      })
      ->toArray();

    // Defensive cards in play
    $card = $this->getCardsInPlay()->reduce(function ($barrel, $card) {
      return $card->getType() === CARD_BARREL && !$card->wasPlayed() && !Rules::isIgnoreCardsInPlay() ? $card : $barrel;
    }, null);
    if (!is_null($card)) {
      $res[] = [
        'id' => $card->getId(),
        'amount' => 1,
        'options' => ['target_types' => [TARGET_NONE]],
      ];
    }

    return [
      'cards' => array_values($res),
      'character' => null,
    ];
  }

  public function getPhaseOneRules($defaultAmount, $isAbilityAvailable = true)
  {
    return [
      RULE_PHASE_ONE_CARDS_DRAW_BEGINNING => $defaultAmount,
      RULE_PHASE_ONE_PLAYER_ABILITY_DRAW => false,
      RULE_PHASE_ONE_CARDS_DRAW_END => 0
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

  /**
   * getBothCharacters: returns randomly chosen two characters to choose from
   * @return array
   */
  public function getBothCharacters()
  {
    return [$this->character, $this->altCharacter];
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
    return [
      'cards' => $this->addOptionsTo($this->getHand()),
      'character' => null,
    ];
  }

  /**
   * Calamity Janet returns Bang + Missed here
   * @return int[]
   */
  public function getBangCardTypes()
  {
    return [CARD_BANG];
  }

  /**
   * addOptionsTo: adds cards options in order to send them to frontend
   * @param Collection $cards
   * @return array
   */
  public function addOptionsTo($cards, $filterNullOptions = true)
  {
    $mustPlayCardId = GameOptions::isEvents() && Globals::getIsMustPlayCard() ? Globals::getMustPlayCardId() : null;
    $cards = $cards
      ->map(function ($card) use ($mustPlayCardId) {
        return [
          'id' => $card->getId(),
          'options' => $card->getPlayOptions($this),
          'type' => $card->getType(),
          'mustPlay' => $card->getId() === $mustPlayCardId,
        ];
      });
    if ($filterNullOptions) {
      $cards = $cards->filter(function ($card) {
        return !is_null($card['options']);
      });
    }
    return $cards->toArray();
  }

  /**
   * playCard: play a card given by id with args to specify the chosen option
   * @param AbstractCard $card
   * @param array $args
   */
  public function playCard($card, $args)
  {
    Notifications::cardPlayed($this, $card, $args);
    $card->play($this, $args);
    Notifications::updateDistances();
    $this->onChangeHand();
  }

  /**
   * attack : performs an attack on all given players
   * @param AbstractCard $card
   * @param int[] $playerIds
   * @param int | null $targetCardId
   * @param boolean $secondMissedNeeded
   * @return void
   */
  public function attack($card, $playerIds, $targetCardId = null, $secondMissedNeeded = false)
  {
    $atom = $this->getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded);
    foreach (array_reverse($playerIds) as $pId) {
      $atom['pId'] = $pId;
      Stack::insertOnTop($atom);
    }
  }

  /**
   * @param AbstractCard $card
   * @param int | null $targetCardId
   * @param boolean $secondMissedNeeded
   * @return array
   */
  public function getReactAtomForAttack($card, $targetCardId, $secondMissedNeeded)
  {
    $srcName = $card->getName();
    if ($this->character == CALAMITY_JANET && $card->getType() == CARD_MISSED) {
      $srcName = clienttranslate('Missed used as a BANG! by Calamity Janet');
    }

    if (is_null($targetCardId)) { // Standard shot to player themselves
      $msgActive = clienttranslate('${you} may react to ${src_name}');
      $msgInactive = clienttranslate('${actplayer} may react to ${src_name}');
      $targetCardName = '';
    } else { // Ricochet to some card in play
      $targetCard = Cards::get($targetCardId);
      $msgActive = clienttranslate('${you} may react to ${src_name} ricocheting to ${target_card_name}');
      $msgInactive = clienttranslate('${actplayer} may react to ${src_name} ricocheting to ${target_card_name}');
      $targetCardName = $targetCard->getName();
    }

    $data = [
      'targetCardId' => $targetCardId,
      'msgActive' => $msgActive,
      'msgInactive' => $msgInactive,
      'src_name' => $srcName,
      'target_card_name' => $targetCardName,
      'src' => $card->jsonSerialize(),
      'attacker' => $this->id,
      'missedNeeded' => 1,
    ];
    if ($secondMissedNeeded) {
      $data['missedNeeded'] = 2;
      $data['msgActive'] = clienttranslate('${you} may react to ${src_name} with ${missedNeeded} Missed!');
      $data['msgInactive'] = clienttranslate('${actplayer} may react to ${src_name} with ${missedNeeded} Missed!');
    }

    return Stack::newAtom(ST_REACT, $data);
  }

  /**
   * react: whenever a player react by passing or playing a card
   */
  public function react($ids)
  {
    $ctxSrc = Stack::getCtx()['src'];
    // If characterId is set, the player was reacting to its ability, not to a card (eg Kit Carlson)
    if (isset($ctxSrc['characterId'])) {
      $this->useAbility($ids);
      return;
    }

    $attackingCard = Cards::getCardByType($ctxSrc['type']);
    if (is_null($ids)) {
      // PASS
      return $attackingCard->pass($this);
    } else {
      if (!is_array($ids)) {
        $ids = [$ids];
      }

      foreach ($ids as $id) {
        $reactionCard = Cards::get($id);
        $attackingCard->react($reactionCard, $this);
        $this->onChangeHand();
        $this->notifyAboutAnotherMissed();
      }
    }
  }

  protected function notifyAboutAnotherMissed()
  {
    $nextAtom = Stack::getNextState();
    $nextAtomIsAttack = isset($nextAtom['type']) && $nextAtom['type'] === 'attack';
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
    $src = $source instanceof Player ? $source->getCharName() : $source->getName();
    $ctx = Stack::getCtx();
    $atom = Stack::newAtom(ST_SELECT_CARD, [
      'src_name' => $src,
      'amountToPick' => $amountToPick,
      'isPrivate' => $isPrivate,
      'toResolveFlipped' => $toResolveFlipped,
      'src' => $source->jsonSerialize(),
      'storeResult' => isset($ctx['storeResult']) && $ctx['storeResult'],
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
    if (array_key_exists('attacker', $ctx) && $ctx['attacker'] != null && $ctx['attacker'] != $this->id) {
      $byPlayer = Players::get($ctx['attacker']);
    }

    // Let characters react => mostly Vulture
    foreach (Players::getLivingPlayers($this->id) as $player) {
      $player->onPlayerEliminated($this);
    }

    // Discard cards
    $this->discardAllCards();
    // Eliminate player
    $forceEliminate = array_key_exists('forceEliminate', $ctx) && $ctx['forceEliminate'];
    // Needs to die for good if:
    // 1. Ghost Town / Dead Man have been played before
    // 2. GT is now and this is the end of a ghost's turn
    // 3. Player has already had their turn during GT event which means it's over for this player but might be applied for others
    if (!EventCards::isResurrectionPossible($this) || $forceEliminate || $this->livingStatus === LIVING_DEAD) {
      bang::get()->eliminatePlayer($this->id);
      $this->eliminated = true;
    } else {
      if (Globals::getEliminatedFirstPId() === 0) {
        Globals::setEliminatedFirstPId($this->id);
      }
      Notifications::playerUnconscious($this);
    }
    $this->save(true);

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
    $allCards = $equipment->merge($hand);
    Cards::discardMany($allCards);
    Notifications::discardedCards($this, $allCards->getIds());
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

  /**
   * swapCharactersIfNeeded: sets correct character chosen by a player and sets a corresponding flag
   * @param int $chosenCharacterId
   */
  public function swapCharactersIfNeeded($chosenCharacterId)
  {
    if (!in_array($chosenCharacterId, [$this->character, $this->altCharacter])) {
      throw new \BgaVisibleSystemException("Character id ${chosenCharacterId} is not in the list of possible characters to choose. Please report a bug.");
    }

    if ($this->altCharacter === $chosenCharacterId) {
      $newParams = [];
      $newParams['player_character'] = $chosenCharacterId;
      $newParams['player_alt_character'] = $this->character;
      $this->altCharacter = $this->character;
      $this->character = $chosenCharacterId;
      self::DB()->update($newParams, $this->id);
    }
  }

  /**
   * setupChosenCharacter: finishes up everything related to character before game starts
   */
  public function setupChosenCharacter()
  {
    $characterObject = Players::getCharacter($this->character);
    $bullets = $characterObject->getBullets();
    if ($this->role === SHERIFF) {
      $bullets++;
    }
    $newParams = [
      'player_character_chosen' => 1,
      'player_hp' => $bullets,
      'player_bullets' => $bullets,
    ];
    self::DB()->update($newParams, $this->id);
  }

  /**
   * @param int $hpAmount
   * @return void
   */
  public function resurrect($hpAmount = 0)
  {
    if ($hpAmount === 0) {
      $params = ['player_unconscious' => 2];
    } else {
      $params = ['player_unconscious' => 0, 'player_hp' => $hpAmount];
    }
    $this->hp = $hpAmount;
    self::DB()->update($params, $this->id);
  }

  public function agreeToDisclaimer()
  {
    self::DB()->update(['player_agreed_to_disclaimer' => true], $this->id);
  }

  /**
   * @param AbstractCard $card
   * @return bool
   */
  public function isCardPlayable($card)
  {
    $handOptions = $this->getHandOptions()['cards'];
    $playableCardsIds = array_map(function ($card) {
      return $card['id'];
    }, $handOptions);
    if (!in_array($card->getId(), $playableCardsIds)) {
      return false;
    }

    $cardOptions = array_values(array_filter($handOptions, function ($cardInHand) use ($card) {
      return $cardInHand['id'] === $card->getId();
    }));
    if (count($cardOptions) === 0) {
      return false;
    }

    if (in_array($card->getType(), [CARD_BANG, CARD_PANIC]) && count($cardOptions[0]['options']['targets']) === 0) {
      return false;
    }
    return true;
  }

  /**
   * We use this method when isCardPlayable() returned that this card is not playable, so we need a reason
   * @param AbstractCard $lastCard
   * @return string
   */
  public function getNonPlayabilityReason($lastCardType)
  {
    switch ($lastCardType) {
      case CARD_MISSED:
        return clienttranslate('Missed! cards could not be played on your turn');
      case CARD_BANG:
      case CARD_PANIC:
        return clienttranslate('distance to all other players is too high');
      default:
        return clienttranslate('no reason actually, please report a bug if you see this message');
    }
  }
}
