<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{

	protected $id;
	protected $game;
	protected $player;
	protected $name;
	protected $text;
	protected $expansion = BASE_GAME;
	protected $bullets;

	public function __construct($pid, $game)
	{
		$this->player = $pid;
		$this->game=$game;
	}

	public function getId() {return $this->id;}
	public function getGame() {return $this->game;}
	public function getName() {return $this->name;}
	public function getText() {return $this->text;}
	public function getExpansion() {return $this->expansion;}
	public function getBullets() {return $this->bullets;}

	public function playCard($id) {
		$card = BangCardManager::getCard($id);
		if($card->play($this)) {
			$name = BangPlayerManager::getPlayer($this->player)->getName();
			$this->game->notifyAllPlayers('cardPlayed', "$name played $card_name.", array('card' => $card, 'player' => $attacker));
		}
	}

	public function selectOption($id) {
		$card = BangCardManager::getCard($this->game->getGameStateValue('currentCard'), $this->game);
		$card->react($id, $this);
	}






	/**
	 * returns the current distance to an enmy.
	 * should not be called on the player checking for targets but on the other players
	 */
	public function getDistanceTo($enemy) {
		$positions = BangPlayerManager::getPlayerPositions();
		$pos1 = $positions[$this->player];
		$pos2 = $positions[$enemy];
		if($pos2 < $pos1) {
			$temp = $pos2;
			$pos2 = $pos1;
			$pos1 = $temp;
		}
		$dist = min($pos2-$pos1, $pos1-$pos2+count($positions));
		$equipment = BangCardManager::getEquipment();
		if(isset($equipment[$this->player])) {
			foreach($equipment[$this->player] as $cid) {
				$card = BangCardManager::getCard($cid);
				if($card->effect['type'] = RANGE_DECREASE) $dist--;
			}
		}
		if(isset($equipment[$enemy])) {
			foreach($equipment[$enemy] as $cid) {
				$card = BangCardManager::getCard($cid);
				if($card->effect['type'] = RANGE_INCREASE) $dist++;
			}
		}
		return $dist;
	}

	public function getPlayersInRange($range) {
		$targets = array();
		$characters = BangPlayerManager::getCharacters();
		foreach($characters as $id=>$char) {
			if($id==$this->player) continue;
			$dist = BangPlayerManager::getCharacter($id, $this->game, true)->getDistanceTo($this->player);
			if($dist <= $range) $targets[] = $id;
		}
		return $targets;
	}

	/**
	 * ask the player, which target
	 */
	function askForTarget($targets, $card) {
		$id = $this->player;
		$t = implode(",",$targets);
		$bplayers = BangPlayerManager::getPlayers($targets);
		$this->game->notifyPlayer($id, 'choosePlayer', '', array('msg' => 'Choose player', 'targets' => $bplayers, 'card' => $card));
		$this->game->setGameStateValue('currentCard', $card);
	}

	/**
	 * attack : performs an attack on all given players
	 */
	public function attack($player_ids) {
		if(count($player_ids) == 1) {
			$id = $player_ids[0];
			$target = BangPlayerManager::getCharacter($id, $this->game, true);
			$target->askReaction($this->player);

		}
		foreach($player_ids as $player_id) {
			// todo use multiactive?
		}
	}

	/**
	 * ask a player for reactions
	 */
	function askReaction($attacker) {
		$id = $this->player;
		$bplayers = BangPlayerManager::getPlayers([$id, $attacker], false);
		$name = $bplayers[$id];
		$attacker_name = $bplayers[$attacker];
		$onHand = BangCardManager::countCards('hand', $id);
		// todo barrel

		if($onHand > 0)  {
			$this->game->setGameStateValue('state',WAIT_REACTION);
			$this->game->setGameStateValue('target',$id);
			$this->game->gamestate->nextState('awaitReaction');
		} else {
			$this->looseLife($attacker);
			$this->game->setGameStateValue('state',PLAY_CARD);
		}

	}

	public function looseLife($byPlayer=-1) {
		$id = $this->player;
		$player = BangPlayerManager::getPlayer($id);
		$hp = $playergetHp()-1;
		$player->setHp($hp);
		$player->save();
		$name = $player->getName();
		$this->game->notifyAllPlayers('lostLife', "$name lost a life", ['id'=>$id, 'hp'=>$hp]);
		$characters = BangPlayerManager::getCharacters();
		$char = characters[$id];
	}

	/**
	 * getRange : Returns the range of players weapon
	 */
	public function getRange() {
		$id = $this->player;
		$cards = BangCardManager::getCardsInPlay($id);
		if(count($cards)>0) {
			$card = new BangCardManager::$classes[$card[0]]();
			return $effect['range'];
		}
		return 1;
	}


}
