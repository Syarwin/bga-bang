<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
{
	private $game;
	private $playerId;
	private $id;
	private $name;
	private $text;
	private $copies = [];
	private $type; // see dbmodel.sql
	private $color;
	private $effect; // array with type, impact and sometimes range


	// TODO : handle the exact copy
	public function __construct($game, $playerId)
  {
    $this->game = $game;
		$this->playerId = $playerId;
	}

// TODO : add all the needed getters
// ----


	public function getUiData()
	{
		return [
			'id'        => $this->id,
			'name'      => $this->name,
			'text'      => $this->text,
			'type'      => $this->type,
			'color'     => $this->color,
			'effect'    => $this->effect,
		];
	}


	public function isPlayable() { return true; }

	/**
	 * play : default function to play a card that. Can be used for cards that have only symbols
	 */
	public function play($player) {
		$players = BangPlayerManager::getPlayers();
		$game = self::getObjectListFromDB("SELECT * FROM game")[0];
		switch ($this->effect['type']) {
			case BASIC_ATTACK:
				$range = 0;
				$player_ids = array();
				$choose = true;
				switch($this->effect['impacts']) {
					case ALL:
						$player_ids = PlayerManager::getLivingPlayers();
						$choose = false;
						break;
					case ALL_OTHER:
						$player_ids = PlayerManager::getLivingPlayers($player);
						$choose = false;
						break;
					case INRANGE:
						$player_ids = $this->getPlayersInRange($player, $this->getRange($player));
						break;
					case SPECIFIC_RANGE:
						$player_ids = $this->getPlayersInRange($player, $this->effect['range']);
						break;
					case ANY:
						$player_ids = PlayerManager::getLivingPlayers();
						break;
				}
				if($choose) {
					return $this->askForTarget($player_ids, $player);
				} else {
					return $this->attack($player_ids, $game, $player);
				}
				break;
			case DRAW:
			case DISCARD:
			case LIFE_POINT_MODIFIER:
			break;
		}

	}

	public function react($id, $game, $player) {
		if($game['game_state']==CHOOSE_PLAYER) {
			switch($this->effect['type']) {
				case BASIC_ATTACK:
					return $this->attack([$id], $game, $player);
					break;
			}
		} elseif($game['game_state'] == WAIT_REACTION ) {
			$player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $player);
			switch($this->effect['type']) {
				case BASIC_ATTACK:
					if($id == 999) {
						$this->looseLife($player, $player_name, $game['game_player']);
					} else {
						$card = self::getObjectListFromDB("SELECT card_type type,card_name name FROM cards WHERE id=" . $this->id)[0];
						if($card->type == 11) {
							self:DbQuery("UPDATE cards SET position=-2 WHERE id=" . $this->id);

							self::DbQuery("UPDATE game SET game_state=0");
							//self::notifyAllPlayers('cardPlayed', "$player_name used " . $card->name, array('card' => $card, 'player' => self::getCurrentPlayerId()));
							//$this->gamestate->nextState( "awaitReaction" );
							return [
								'nextState' => 'awaitReaction',
								'notifs' => [
									['notif' => 'cardPlayed', 'msg' => "$player_name used " . $card->name, 'args' => ['card' => $card, 'player' => $player]]
								]
							];
						} else {
							//todo show error
						}
					}
					break;
			}
		}
	}

	/**
	 * getRange : Returns the range of player
	 */
	public function getRange($player) {
		$cards = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_position=$player AND card_onHand=false");
		if(count($cards)>0) {
			$card = new BangCardManager::$classes[$card[0]]();
			return $effects->range;
		}
		return 1;
	}

	/**
	 * getDistance : returns all players within a given range to a player
	 */
	public function getPlayersInRange($player, $range) {
		$targets = array();
		$characters = BangPlayerManager::getCharacters();
		$equipment = BangCardManager::getEquipment($player);
		$decrease = 0;
		if(isset($equipment[$player])) {
			foreach($equipment[$player] as $card) {
				if($card->effects->type==RANGE_DECREASE) $decrease += 1;
			}
		}
		if($characters[$player] == ROSE_DOOLAN) $decrease += 1;

		$positions = array_flip(self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no", true));

		foreach($positions as $player_id => $pos) {
			if($player==$player_id) continue;
			$min = min($positions[$player], $pos);
			$max = max($positions[$player], $pos);
			$dist = min($max-$min,count($positions)+$min-$max);

			if(isset($equipment[$player_id])) {
				$cards = $equipment[$player_id];
				foreach($cards as $card_id => $card) {
					if($card->effects['type']==RANGE_INCREASE) $dist += 1;
				}
			}
			if($characters[$player_id] == PAUL_REGRET) $dist += 1;
			if($range >= $dist-$decrease)
				$targets[] = $player_id;
		}
		return $targets;
	}

	/**
	 * ask the player, which target
	 */
	function askForTarget($targets, $player_id) {
		$t = implode(";",$targets);
		$id = $this->id;
		self::DbQuery("UPDATE game SET game_state=1, game_text='Choose player', game_options='$t', game_card=$id");
		$t = implode(",",$targets);
		$names = self::getCollectionFromDB("SELECT player_id, player_name name, player_color color FROM player WHERE player_id in ($t)");
		return ["notifs"=>[
			['recipient'=>$player_id, 'notif'=>'choosePlayer', 'msg'=> '', 'args'=>['msg' => 'Choose player', 'targets' => $names, 'card' => $id]]
			]];
		//self::notifyPlayer($player_id, 'choosePlayer', '', array('msg' => 'Choose player', 'targets' => $names, 'card' => $id));
	}

	/**
	 * ask a player for reactions
	 */
	function askReaction($target, $msgToTarget, $msgToAll, $cards, $card, $player) {

		self::DbQuery("UPDATE game SET game_state=2, game_text='$msgToTarget', game_options='".implode(";",$cards)."', game_card=$card, game_target=$target");
		//$this->gamestate->nextState( "awaitReaction" );
		return [
			'nextState' => "awaitReaction",
			'notifs'=> [
				['notif'=>'cardPlayed', 'msg'=> $msgToAll, 'args'=>['card' => $card, 'player' => $player]],
				['recipient'=>$target, 'notif'=>'chooseReaction', 'msg'=> '', 'args'=>['msg'=>$msgToTarget]]
			]
		];
		//self::notifyAllPlayers('cardPlayed', $msgToAll, array('card' => $card, 'player' => self::getCurrentPlayerId()));
		//self::notifyPlayer($target, 'chooseReaction', '', array('msg'=>$msgToTarget, 'game_options' => $cards));
	}

	/**
	 * attack : performs an attack on all given players
	 */
	public function attack($player_ids, $game, $player) {
		$player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=".$game['game_player']);
		if(count($player_ids) == 1) {
			$id = $player_ids[0];
			$target = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=$id");
			$cards = self::getObjectListFromDB("SELECT card_id hand FROM cards WHERE card_position=$id AND card_type%10=1",true);
			$onHand = self::getUniqueValueFromDB("SELECT count(*) hand FROM cards WHERE card_position=$id AND card_onHand=1");
			if($onHand > 0 || count($cards>0))
				return $this->askReaction($id, "$player_name attacked you. Choose, how to react",
					"$player_name played Bang! on $target. $target may react.",$cards, $this->id, $player);
			else
				return $this->looseLife($id, "$player_name played Bang! on $target. $target can't react");
			return;
		}
		foreach($player_ids as $player_id) {

		}
	}


	public function looseLife($id, $msg, $byPlayer=-1) {
		$hp = self::getUniqueValueFromDB("SELECT char_lp FROM characters WHERE char_player = $id")-1;
		$notifs = array();
		$notifs[] = ['notif'=>'lostLife', 'msg' => "$name lost a life", 'args' => ['id'=>$id, 'hp'=>$hp]];
		self::DbQuery("UPDATE characters SET char_lp=$hp");
		$characters = BangPlayerManager::getCharacters();
		$char = characters[$id];
		if($char == EL_GRINGO && $byPlayer>-1) {
			$player = self::getObjectListFromDB("SELECT player_id FROM player", true);
			$cards = self::getObjectListFromDB("SELECT card_id id, card_name name FROM cards WHERE card_position=$byPlayer AND card_onHand=1");

			$n = rand(0,count($cards)-1);
			$card = $cards[$n];

			$hands = self::getCollectionFromDB("SELECT card_position, COUNT(*) FROM cards WHERE card_position>0 GROUP BY card_position", true);

			self::DbQuery("UPDATE cards SET card_position=$id WHERE card_id=" . $card['id']);
			$players = BangPlayerManager::getPlayers();
			foreach($players as $player) {
				$pid = $player['id'];
				if($pid==$id) {
					$hand = BangCardManager::getHand($pid);
					$notifs[] = ['recipient'=> $pid, 'notif'=>'handChange', 'msg' => "$name steals a card from his attacker",
									'args' => ['hands'=>$hands, 'hand'=>$hand, 'card' => $card, 'gain'=>$id, 'loose'=>$byPlayer]];
				} elseif($pid==$byPlayer) {
					$hand = BangCardManager::getHand($pid);
					$notifs[] = ['recipient'=> $pid, 'notif'=>'handChange', 'msg' => "$name steals a card from you",
									'args' => ['hands'=>$hands, 'hand'=>$hand, 'card' => $card, 'gain'=>$id, 'loose'=>$byPlayer]];
				} else {
					$notifs[] = ['recipient'=> $pid, 'notif'=>'handChange', 'msg' => "$name steals a card from his attacker",
									'args' => ['hands'=>$hands, 'gain'=>$id, 'loose'=>$byPlayer]];
				}
			}
			$notifs[] = ['notif'=>'handChange', 'msg' => "$name steals a card from his attacker", 'args' => ['hands'=>$hands, 'card' => $card, 'gain'=>$id, 'loose'=>$byPlayer]];
			$notifs[] = ['recipient'=> $byPlayer, 'notif'=>'','msg' => "$name stole " . $card->name . " from you"];
		}
		if($char == BART_CASSIDY) {
			// todo
		}
		return ["notifs"=>$notifs];
	}

	public function createDebug($args) {
		return ['notifs'=>[['notif'=>'debug', 'msg'=>'', 'args'=>$args]]];
	}
}
