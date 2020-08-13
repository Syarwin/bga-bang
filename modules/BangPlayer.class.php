<?php

/*
 * BangPlayer: all utility functions concerning a player
 */
class BangPlayer extends APP_GameClass
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
      $this->hp = (int)$row['player_score'];
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = $row['player_role'];
      $this->bullets = (int)$row['player_bullets'];
    }
  }

  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function getHp(){ return $this->hp; }
  public function getRole(){ return $this->role; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function getPosition(){ return BangPlayerManager::getPlayerPositions()[$this->id]; }
  public function getText() { return $this->text;}
  public function getExpansion() { return $this->expansion;}
  public function getBullets() { return $this->bullets;}
  public function getCardsInHand(){ return BangCardManager::getHand($this->id); }
  public function getRandomCardInHand(){
    $cards = self::getCardsInHand();
    if(empty($cards)){
      throw new BgaVisibleSystemException("Cannot draw a card in an empty hand");
    }
    shuffle($cards);
    return $cards[0];
  }
  public function getCardsInPlay(){ return BangCardManager::getCardsInPlay($this->id); }
  public function countCardsInHand() { return BangCardManager::countCards("hand", $this->id);}

  public function setHp($hp){ $this->hp = $hp; }

  public function getUiData($currentPlayerId = null) {
    $current = $this->id == $currentPlayerId;
    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
      'hand' => ($current) ? array_values(BangCardManager::getHand($this->id, true)) : BangCardManager::countCards('hand', $this->id),
      'role' => ($current || $this->role==SHERIFF) ? $this->role : null,
      'inPlay' => array_values(BangCardManager::getCardsInPlay($this->id, true)),
      'characterId' => $this->character,
      'character' => $this->character_name,
      'powers' => $this->text,
      'hp' => $this->hp,
      'bullets' => $this->bullets
    ];
  }

  /**
   * saves eliminated status and hp to the database
   */
  public function save() {
    $eliminated = ($this->eliminated) ? 1 : 0;
    $sql = "UPDATE player SET player_eliminated=$eliminated, player_score= " . $this->hp . " WHERE player_id=" . $this->id;
    self::DbQuery($sql);
  }

  public function startOfTurn() {
    $cards = BangCardManager::deal($this->id, 2);
    BangNotificationManager::gainedCard($this, $cards);
  }

  public function playCard($id, $args) {
    $state = bang::$instance->gamestate->state()['name'];
    if($state == 'multiReact' || $state == 'react') {
      $this->react($id);
      return;
    }

		$card = BangCardManager::getCard($id);
		BangNotificationManager::cardPlayed($this, $card, $args);
    bang::$instance->setGameStateValue('currentCard', $id);
    if($card->play($this, $args)) {
      bang::$instance->gamestate->nextState( "continuePlaying" );
    }

	}

	public function react($id) {
		$card = BangCardManager::getCard(bang::$instance->getGameStateValue('currentCard'));
		$card->react($id, $this);
    bang::$instance->notifyAllPlayers('debug', bang::$instance->gamestate->state()['name'], []);
    if(bang::$instance->gamestate->state()['name'] == 'multiReact')
      bang::$instance->gamestate->setPlayerNonMultiactive($this->getId(), 'finishedReaction');
    else {
      bang::$instance->gamestate->nextState( "react" );
    }
	}

  public function getHandOptions() {
    $hand = BangCardManager::getHand($this->id);

    $options = [];
    foreach($hand as $card) {
      $options[] = ['id'=>$card->getId(), 'options'=>$card->getPlayOptions($this)];
    }
    return array_values($options);
  }

  /**
   * returns the current distance to an enmy.
   * should not be called on the player checking for targets but on the other players
   */
  public function getDistanceTo($enemy) {
    $positions = BangPlayerManager::getPlayerPositions();
    $pos1 = $positions[$this->getId()];
    $pos2 = $positions[$enemy->getId()];
    if($pos2 < $pos1) {
      $temp = $pos2;
      $pos2 = $pos1;
      $pos1 = $temp;
    }
    $dist = min($pos2-$pos1, $pos1-$pos2+count($positions));
    foreach($this->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] = RANGE_DECREASE) $dist--;
    }
    foreach($enemy->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] = RANGE_INCREASE) $dist++;
    }
    return $dist;
  }

  public function isInRange($enemy, $range){
    return $enemy->getDistanceTo($this) <= $range;
  }

  public function getPlayersInRange($range) {
		$targets = BangPlayerManager::getPlayers();
    Utils::filter($targets, function($player) use ($range){
      return $player->id != $this->id && $this->isInRange($player, $range);
    });

    return array_map(function($target){ return $target->getId(); }, $targets);
	}

  /**
   * attack : performs an attack on all given players
   */
  public function attack($playerIds) {
    $reactions = [];
    foreach(BangPlayerManager::getPlayers($playerIds) as $player){
      // Player has defensive equipment ? (eg Barrel)
      $missedWithEquipment = array_reduce($player->getCardsInPlay(), function($missed, $card){
        return $missed || ($card->getEffectType() == DEFENSIVE && $card->play());
      });
      if($missedWithEquipment) continue;

      // Otherwise, player has at least one card in hand ?
      if($player->countCardsInHand() > 0)  {
  			$reactions[] = $player->id; // Give him a chance to (pretend to) react
  		} else {
  			$this->looseLife($player); // Lost life immediatly
  		}
    }

    // Go to corresponding state
    if(count($reactions) == 1) {
			bang::$instance->setGameStateValue('target', $reactions[0]);
			bang::$instance->gamestate->nextState('awaitReaction');
      return false;
    } elseif(count($reactions) > 1) {
      BangPlayerManager::preparePlayerActivation($reactions);
      bang::$instance->gamestate->nextState('awaitMultiReaction');
      return false;
    }
    return true;
  }

	/**
	 * ask a player for reactions
	 */
	public function askReaction($attacker) {
		$id = $this->id;
		$onHand = BangCardManager::countCards('hand', $id);
		// todo barrel

		if($onHand > 0)  {
			bang::$instance->setGameStateValue('target',$id);
			bang::$instance->gamestate->nextState('awaitReaction');
		} else {
			$this->looseLife($attacker);
		}
	}

  public function getDefensiveCards() {
    $hand = BangCardManager::getHand($this->id);
    $res = [];
    foreach($hand as $card) {
      if($card->getColor() == BROWN && $card->getEffectType() == DEFENSIVE)
        $res[] = ['id' => $card->getID(), 'options' => ['type' => OPTION_NONE]];
    }
    return array_values($res);
  }

	public function looseLife($byPlayer=-1) {
		$this->hp--;
    if($this->hp == 0) $this->eliminate();
		$this->save();
    BangNotificationManager::lostLife($this);
	}

  public function eliminate($byPlayer = -1){
    $this->eliminated = true;
  }

	/**
	 * getRange : Returns the range of players weapon
	 */
	public function getRange() {
    $weapon = $this->getWeapon();
    return is_null($weapon)? 1 : $weapon->getEffect()['range'];
	}

  public function getWeapon(){
    return array_reduce($this->getCardsInPlay(), function($weapon, $card){
      $effect = $card->getEffect();
      return ($effect['type'] == WEAPON) ? $card : $weapon;
    }, null);
  }

  public function discardWeapon(){
    $weapon = $this->getWeapon();
    if(!is_null($weapon)) {
      BangCardManager::playCard($weapon->getId());
    }
  }
}
