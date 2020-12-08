<?php
namespace Bang\Characters;
use Bang\Cards\Cards;
use Bang\Cards\Card;
use Bang\Game\Utils;
use Bang\Game\Notifications;
use Bang\Game\Log;

/*
 * Player: all utility functions concerning a player
 */
class Player extends \APP_GameClass
{
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


  public function __construct($row) {
    if($row != null) {
      $this->id = $row['player_id'];
      $this->no = (int)$row['player_no'];
      $this->name = $row['player_name'];
      $this->color = $row['player_color'];
      $this->eliminated = $row['player_eliminated'] == 1;
      $this->hp = (int)$row['player_hp'];
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = $row['player_role'];
      $this->bullets = (int)$row['player_bullets'];
    }
  }

  /*
   * Getters
   */
  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function getHp(){ return $this->hp; }
  public function getRole(){ return $this->role; }
  public function getCharName() { return $this->character_name; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function getPosition(){ return Players::getPlayerPositions()[$this->id]; }
  public function getText() { return $this->text;}
  public function getExpansion() { return $this->expansion;}
  public function getBullets() { return $this->bullets;}
  public function getCardsInHand($formatted = false){ return Cards::getHand($this->id, $formatted); }
  public function getCardsInPlay(){ return Cards::getCardsInPlay($this->id); }
  public function countCardsInHand() { return Cards::countCards("hand", $this->id);}
  public function setHp($hp) {$this->hp = $hp;}

  public function getUiData($currentPlayerId = null) {
    $current = $this->id == $currentPlayerId;
    return [
      'id'        => $this->id,
      'eliminated'=> $this->eliminated,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
      'characterId' => $this->character,
      'character'   => $this->character_name,
      'powers'      => $this->text,
      'hp'          => $this->hp,
      'bullets'     => $this->bullets,
      'hand' => ($current) ? array_values(Cards::getHand($this->id, true)) : Cards::countCards('hand', $this->id),
      'role' => ($current || $this->role==SHERIFF || $this->eliminated) ? $this->role : null,
      'inPlay' => array_values(Cards::getCardsInPlay($this->id, true)),
    ];
  }


  /**
   * saves eliminated status and hp to the database
   */
  public function save() {
    $eliminated = ($this->eliminated) ? 1 : 0;
    $sql = "UPDATE player SET player_eliminated=$eliminated, player_hp= " . $this->hp . " WHERE player_id=" . $this->id;
    self::DbQuery($sql);
  }



/*************************
********** Utils *********
*************************/

  /*
   * Draw $amount card from deck and notify them
   */
  public function drawCards($amount){
    $cards = Cards::deal($this->id, $amount);
    Notifications::drawCards($this, $cards);
  }

  /*
   * Discard a card and notify (with/without a message) it
   */
  public function discardCard($card, $silent = false){
    $card->discard();
    Notifications::discardedCard($this, $card, $silent);
    $this->onCardsLost();
  }

  /*
   * Discard its weapon
   */
  public function discardWeapon(){
    $weapon = $this->getWeapon();
    if(!is_null($weapon))
      $this->discardCard($weapon, true);
  }


  /*
   * Draw! (careful, not the same as drawCard), notify and return the card
   */
  public function draw($args, $src) {
    $card = Cards::draw();
    Notifications::drawCard($this, $card, $src);
    return $card;
  }


  /**
   * incresase the life points of a player.
   */
  public function gainLife($amount = 1) {
    if($this->hp == $this->bullets) return;

    $this->hp += $amount;
    if($this->hp > $this->bullets) $this->hp = $this->bullets;
    $this->save();
    Notifications::gainedLife($this, $amount);
	}

  /**
   * reduces the life points of a player by 1.
   * return: whether the player was eliminated
   */
  public function looseLife($amount = 1) {
		$this->hp -= $amount;
    $this->save();
    Notifications::lostLife($this, $amount);
    if($this->hp <= 0) {
      if(Utils::getStateName() == 'multiReact') return null;
      Log::addAction('lastState', [Utils::getStateName()]);
      return 'eliminate';
    }
    $this->save();
    return null;
	}



/************************************
********** Advanced getters *********
************************************/

  /*
   * Return a random card from the hand (useful for drawing in hand for instance)
   */
  public function getRandomCardInHand(){
    $cards = self::getCardsInHand();
    if(empty($cards)){
      throw new \BgaVisibleSystemException("Cannot draw a card in an empty hand");
    }
    shuffle($cards);
    return $cards[0];
  }


  /**
   * returns the current distance to an enmy from the view of the enemy
   * should not be called on the player checking for targets but on the other players
   */
  public function getDistanceTo($enemy) {
    $positions = Players::getPlayerPositions();
    $pos1 = $positions[$this->getId()];
    $pos2 = $positions[$enemy->getId()];
    $d = abs($pos2 - $pos1);
    $dist = min($d, count($positions) - $d);
    foreach($enemy->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] == RANGE_DECREASE) $dist--;
    }
    foreach($this->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] == RANGE_INCREASE) $dist++;
    }
    return $dist;
  }


  public function isInRange($enemy, $range){
    return ($enemy->getDistanceTo($this) <= $range);
  }


  /**
  * getPlayerInRange : Returns the players ids in range of weapon
  */
  public function getPlayersInRange($range) {
		$targets = Players::getLivingPlayers(null, true);

    Utils::filter($targets, function($player) use ($range){
      return $this->isInRange($player, $range); //($player->getDistanceTo($this) <= $range); // TODO : use isInRange => weird bug...
    });

    return array_map(function($target){ return $target->getId(); }, $targets);
	}


  /**
  * getWeapon : Returns weapon card of player, or null if not equipped
  */
  public function getWeapon(){
    return array_reduce($this->getCardsInPlay(), function($weapon, $card){
      $effect = $card->getEffect();
      return ($effect['type'] == WEAPON) ? $card : $weapon;
    }, null);
  }

  /**
  * getRange : Returns the range of player's weapon
  */
  public function getRange() {
    $weapon = $this->getWeapon();
    return is_null($weapon)? 1 : $weapon->getEffect()['range'];
  }


  public function hasUnlimitedBangs() {
    $weapon = $this->getWeapon();
    return !is_null($weapon) && $weapon->getType() == CARD_VOLCANIC;
  }


  public function hasPlayedBang(){
    return !is_null(Log::getLastAction("bangPlayed", $this->id));
  }


  /*
   * return the list of bang cards (for indians and duel for instance)
   */
  public function getBangCards() {
    $hand = $this->getCardsInHand();
    Utils::filter($hand, function($card){
      return $card->getType() == CARD_BANG;
    });
    $cards = array_map(function($card){
      return [
        'id' => $card->getId(),
        'options' => ['type' => OPTION_NONE],
        'amount' => 1
      ];
    }, $hand);

    return [
      'cards' => $cards,
      'character' => null,
    ];
  }


  /*
   * return defensive options
   */
  public function getDefensiveOptions() {
    $args = Log::getLastAction('cardPlayed');
    $amount = isset($args['missedNeeded']) ? $args['missedNeeded'] : 1;
    // Defensive cards in hand
    $hand = $this->getCardsInHand();
    Utils::filter($hand, function($card){ return $card->getColor() == BROWN && $card->getEffectType() == DEFENSIVE; });
    $res = array_map(function($card) use ($amount) { return ['id' => $card->getId(), 'amount' => $amount, 'options' => ['type' => OPTION_NONE] ]; }, $hand);

    // Defensive cards in play
    $card = array_reduce($this->getCardsInPlay(), function($barrel, $card){
      return ($card->getType() == CARD_BARREL && !$card->wasPlayed()) ? $card : $barrel;
    }, null);
    if(!is_null($card)) $res[] = ['id' => $card->getId(), 'amount' => 1,  'options' => ['type' => OPTION_NONE]];

    return ['cards' => array_values($res), 'character' => null];
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
  public function startOfTurn() {
    $equipment = $this->getCardsInPlay();
		// make sure dynamite gets handled before jail
		Utils::sort($equipment, function($a, $b) { return $a->getType() > $b->getType();});
    return array_reduce($equipment, function($state, $card){
      if($state == 'skip' || $card->getEffectType() != STARTOFTURN) return $state;
      $newState = $card->activate($this);
      return $newState ?: $state;
    }, 'draw');
  }


  /**
   * getHandOptions: give the list of playable cards in hand, along with their options
   */
  public function getHandOptions() {
    $options = array_map(function($card){
      return [
        'id' => $card->getId(),
        'options' => $card->getPlayOptions($this)
      ];
    }, $this->getCardsInHand() );
    Utils::filter($options, function($card) { return !is_null($card['options']); });
    return [
      'cards' => array_values($options),
      'character' => null
    ];
  }


  /**
   * playCard: play a card given by id with args to specify the chosen option
   */
  public function playCard($id, $args) {
		$card = Cards::getCard($id);
    Notifications::cardPlayed($this, $card, $args);
    Log::addCardPlayed($this, $card, $args);
    $newstate = $card->play($this, $args);
    $this->onCardsLost();
    return $newstate;
	}


  /**
   * react: whenever a player react by passing or playing a card
   */
	public function react($ids) {
    $action = Log::getLastActions(["selection", "react"])[0];
    $args = json_decode($action['action_arg'], true);
    $src = $action['action'] == "react" ? $args['_private'][$this->id]['src'] : Cards::getCurrentCard();

    // Beer reaction when dying
    if($src == 'hp') {
      if(is_null($ids)) { // PASS
        // nothing to do, i think
      } else {
       foreach($ids as $i) {
          $card = Cards::getCard($i);
          Cards::discardCard($card);
          Notifications::cardPlayed($this, $card, []);
          Log::addCardPlayed($this, $card, $args);
        }
      }
      return null;
    }

    // "Normal" react
    else {
  		$card = ($src instanceof Card) ? $src : Cards::getCard($src);
      if(is_null($ids)) // PASS
        return $card->pass($this);
      else {
        $newstate = null;
        if(!is_array($ids))
          $ids = [$ids];

        foreach($ids as $id) {
          $reactionCard = Cards::getCard($id);
          $newstate = $card->react($reactionCard, $this);
          $this->onCardsLost();
        }
        return $newstate;
      }
    }
	}


  /**
   * attack : performs an attack on all given players
   */
  public function attack($playerIds, $checkMissed = true) {
    $reactions = [];
    $state = null;
    foreach(Players::getPlayers($playerIds) as $player){
      // Player has defensive equipment ? (eg Barrel)
      $reaction = [];
      if($checkMissed) {
        $reaction = $player->getDefensiveOptions();
      } else {
        $reaction = $player->getBangCards();
      }

      $handcount = $player->countCardsInHand();

      if(count($reaction['cards']) > 0 || $handcount > 0 || $reaction['character'] != null) {
        $reaction['src'] = Log::getCurrentCard();
        $reactions[$player->getId()] = $reaction; // Give him a chance to (pretend to) react
  		} else {
        $curr = Players::getCurrentTurn();
        $byPlayer = $this->id==$curr ? $this : null;
  			$newstate = $player->looseLife(); // Lost life immediatly
        if(!is_null($newstate)) $state = $newstate;
  		}
    }



    // Go to corresponding state
    if(count($reactions) > 0) {
      $card = Cards::getCard(Log::getCurrentCard());

      $src = $card->getName();
      if($this->character == CALAMITY_JANET && $card->getType() == CARD_MISSED)
        $src = clienttranslate("Missed used as a BANG! by Calamity Janet");

      $inactive = count($reactions) > 1 ? clienttranslate('Players may react to ${src}') : clienttranslate('${actplayer} may react to ${src}');
      $args = [
        'msgActive' => clienttranslate('${you} may react to ${src}'),
        'msgInactive' => $inactive,
        'src' => $src,
        'attack' => true,
        '_private' => $reactions
      ];
      Log::addAction("react", $args);

      return "react";
    }
    return $state;
  }

  public function eliminate(){
    // get player who eliminated this player
    $byPlayer = Players::getCurrentTurn(true);
    if($byPlayer->id == $this->id) $byPlayer = null;

    // let characters react
    foreach(Players::getLivingPlayers($this->id, true) as $player)
      $player->onPlayerEliminated($this);

    //discard cards
    $hand = $this->getCardsInHand();
    $equipment = $this->getCardsInPlay();
    foreach (array_merge($hand, $equipment) as $card) Cards::discardCard($card);
    Notifications::discardedCards($this, $equipment, true);
    Notifications::discardedCards($this, $hand, false);

    // eliminate player
    $this->eliminated = true;
    $this->save();
    //bang::$instance->eliminatePlayer($this->id);
    Notifications::playerEliminated($this);

    //check if game should end
    if(Players::countRoles([SHERIFF]) == 0){
      $living = Players::getLivingPlayers(null, true);
      if(count($living) == 0) {
        Players::setWinners([SHERIFF, DEPUTY, OUTLAW, RENEGADE]);
      } elseif(count($living) > 1 || $living[0]->role == OUTLAW) {
        Players::setWinners([OUTLAW]);
      } else {
        Players::setWinners([RENEGADE]);
      }
      return "endgame";
    }

    if((Players::countRoles([OUTLAW, RENEGADE]) == 0)) {
      Players::setWinners([SHERIFF, DEPUTY]);
      return "endgame";
    }



    //handle rewards/penalties
    if(!is_null($byPlayer)) {
      if($this->getRole() == OUTLAW) {
        $byPlayer->drawCards(3);
      }
      if($this->getRole() == DEPUTY && $byPlayer->getRole() == SHERIFF) {
        Notifications::tell("The Sheriff eliminated his Deputy and must discard all cards",[]);
        $hand = $byPlayer->getCardsInHand();
        $equipment = $byPlayer->getCardsInPlay();
        foreach (array_merge($hand, $equipment) as $card) Cards::discardCard($card);
        Notifications::discardedCards($byPlayer, $equipment, true);
        Notifications::discardedCards($byPlayer, $hand, false);
      }
    }
  }


  /***************************************
  ****************************************
  ************** templates ***************
  ****************************************
  ***************************************/



  public function useAbility($args) {}

  /**
   * called whenever a card from the hand is lost(played, stolen, discarded, etc)
   * atm just for Suzy
   */
  public function onCardsLost() {} //todo löschen wenn es mit checkHand funktioniert

  public function checkHand() {}

  /**
   * called whenever a player is eliminated
   * atm just for Vulture Sam
   */
  public function onPlayerEliminated($player) {}


  public function getAmountToCounterBang() {return 1;}



}
