<?php

/*
 * BangCharacter: base class to handle characters
 */
class BangCharacter extends APP_GameClass
{

	public $game;
	public $player;
	public $name;
	public $text;
	public $expansion = BASE_GAME;
	public $implemented = false;
	public $bullets;

	public function __construct()
	{
	}
	
	public function playCard($id) {
		$card = BangCardManager::getCard($id);
		$card->id = $id;
		if($card->play($this)) {			
			$name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $this->player);
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
		$positions = array_flip(self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no", true));
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
		$names = self::getCollectionFromDB("SELECT player_id, player_name name, player_color color FROM player WHERE player_id in ($t)");		
		$this->game->notifyPlayer($id, 'choosePlayer', '', array('msg' => 'Choose player', 'targets' => $names, 'card' => $card));
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
			
		}		
	}
	
	/**
	 * ask a player for reactions
	 */
	function askReaction($attacker) {	
		$id = $this->player;
		$name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=$id");
		$attacker_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=$attacker");
		$cards = self::getObjectListFromDB("SELECT card_id hand FROM cards WHERE card_position=$id AND card_type%10=1",true);
		$onHand = self::getUniqueValueFromDB("SELECT count(*) hand FROM cards WHERE card_position=$id AND card_onHand=1");
		
		
		if($onHand > 0 || count($cards>0))  {
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
		$hp = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id=$id")-1;	
		
		$name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=$id");
		$this->game->notifyAllPlayers('lostLife', "$name lost a life", ['id'=>$id, 'hp'=>$hp]);
		self::DbQuery("UPDATE player SET player_score=$hp WHERE player_id= ". $id);		
		$characters = BangPlayerManager::getCharacters();
		$char = characters[$id];		
		
	}
	
	/**
	 * getRange : Returns the range of players weapon
	 */
	public function getRange() {
		$id = $this->player;
		$cards = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_position=$id AND card_onHand=false");
		if(count($cards)>0) {
			$card = new BangCardManager::$classes[$card[0]]();
			return $effect['range'];
		}
		return 1;
	}
	
	
}
