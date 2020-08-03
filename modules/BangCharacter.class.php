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
		$res = $card->play($player_id);
		$notifs = $res['notifs'];
		
	}
	
	public function selectOption($id) {
		$card = BangCardManager::createCard($game->getGameStateValue('game_card'), $this->game);
		$card->react($id, $this->player);
	}
	
	
	/**
	 * getRange : Returns the range of player
	 */
	public static function getRange($pid) {
		$cards = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_position=$pid AND card_onHand=false");
		if(count($cards)>0) {
			$card = new BangCardManager::$classes[$card[0]]();
			return $effect['range'];
		}
		return 1;
	}
	
	/**
	 * returns the current distance to an enmy.
	 * should not be called on the player checking for targets but on the other players
	 */
	public function getDistanceTo($enemy) {
		$positions = array_flip(self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no", true));
		$pos1 = $positions[$this->player];
		$pos2 = $positions[$enemy]
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
	}
	
	public function getPlayersInRange($range) {
		$targets = array();
		$characters = self::getCharacters();
		for($characters as $id=>$char) {
			$dist = BangPlayerManager::getCharacter($id, $this->game, true).getDistanceTo($this->player);
			if($dist <= $range) $targets[] = $id;
		}
		return $targets;
	}
}
