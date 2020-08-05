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
  protected $character_name
	protected $text;
	protected $bullets;
  protected $expansion = BASE_GAME;


  public function __construct($row) {
    if($row != null) {
      $this->id = $row['player_id'];
      $this->no = $row['player_no'];
      $this->name = $row['player_name'];
      $this->color = $row['player_color'];
      $this->eliminated = $row['player_eliminated'] == 1;
      $this->hp = $row['player_score'];
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = $row['player_role'];
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

  public function getText() {return $this->text;}
  public function getExpansion() {return $this->expansion;}
  public function getBullets() {return $this->bullets;}



  public function setHp($hp){ $this->hp = $hp; }

  public function getUiData($currentPlayerId = null) {
    $current = $this->id == $currentPlayerId;
    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
      'hand' => ($current) ? array_values(BangCardManager::getHand($currentPlayerId)) : BangCardManager::countCards('hand', $currentPlayerId),
      'role' => ($current || $this->role==SHERIFF) ? $this->role : null,
      'character' => $this->character_name,
      'powers' => $this->text,
      'bullets' => $this->bullets
    ];
  }

  /**
   * saves eliminated status and hp to the database
   */
  public function save() {
    $eliminated = ($this->eliminated) ? 1 : 0;
    $sql = "UPDATE players SET player_eliminated=$eliminated, player_score=" . $this->score;
    self::DbQuery($sql);
  }

  public function startOfTurn() {

  }

  public function playCard($id) {
		$card = BangCardManager::getCard($id);
		if($card->play($this)) {
			$name = $this->name;
			BangNotificationManager::cardPlayed($card, $this);
		}
	}

	public function selectOption($id) {
		$card = BangCardManager::getCard(bang::$instance->getGameStateValue('currentCard'));
		$card->react($id, $this);
	}

  public function getHandOptions() {
    $hand = BangCardManager::toObjects(BangCardManager::getHand($this->id))
    $options = [];
    for($hand as $card) {
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
    $pos1 = $positions[$this->id];
    $pos2 = $positions[$enemy];
    if($pos2 < $pos1) {
      $temp = $pos2;
      $pos2 = $pos1;
      $pos1 = $temp;
    }
    $dist = min($pos2-$pos1, $pos1-$pos2+count($positions));
    $equipment = BangCardManager::getEquipment();
    if(isset($equipment[$this->id])) {
      foreach($equipment[$this->id] as $cid) {
        $card = BangCardManager::getCard($cid);
        if($card->getEffect()['type'] = RANGE_DECREASE) $dist--;
      }
    }
    if(isset($equipment[$enemy])) {
      foreach($equipment[$enemy] as $cid) {
        $card = BangCardManager::getCard($cid);
        if($card->getEffect()['type'] = RANGE_INCREASE) $dist++;
      }
    }
    return $dist;
  }

  public function getPlayersInRange($range) {
		$targets = array();
		$bplayers = BangPlayerManager::getPlayers();
		foreach($bplayers as $id=>$char) {
			if($id==$this->id) continue;
			$dist = BangPlayerManager::getPlayer($id)->getDistanceTo($this->id);
			if($dist <= $range) $targets[] = $id;
		}
		return $targets;
	}

  /**
   * attack : performs an attack on all given players
   */
  public function attack($player_ids) {
    if(count($player_ids) == 1) {
      $id = $player_ids[0];
      $target = BangPlayerManager::getPlayer($id);
      $target->askReaction($this->player);
    }
    foreach($player_ids as $player_id) {
      // todo use multiactive?
    }
  }

	/**
	 * ask a player for reactions
	 */
	public function askReaction($attacker) {
		$id = $this->player;
		$onHand = BangCardManager::countCards('hand', $id);
		// todo barrel

		if($onHand > 0)  {
			bang::instance->setGameStateValue('state',WAIT_REACTION);
			bang::instance->setGameStateValue('target',$id);
			bang::instance->gamestate->nextState('awaitReaction');
		} else {
			$this->looseLife($attacker);
			bang::instance->setGameStateValue('state',PLAY_CARD);
		}
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
		$cards = BangCardManager::getCardsInPlay($this->id);
		for($cards as $cid=>$card) {
			$effect = BangCardManager::getCard($cid)->getEffect();
			if(($effect['type']) == WEAPON) return $effect['range'];
		}
		return 1;
	}

}
