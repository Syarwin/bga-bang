<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
{

	public function __construct(){
	}

	public $id;
	public $name;
	public $text;
	public $copies = [];
	public $implemented = false;
	public $type; // see dbmodel.sql
	public $color;
	public $effect; // array with type, impact and sometimes range

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
						$player_ids = $this->getPlayersInRange($player, BangCharacter::getRange($player));
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
					return $this->attack($player_ids, $player);
				}
				break;
			case DRAW:
			case DISCARD:
			case LIFE_POINT_MODIFIER:
			break;
		}
		
	}
	
	public function react($id, $player) {
		if($this->game->getGameStateValue('state')==CHOOSE_PLAYER) {
			switch($this->effect['type']) {
				case BASIC_ATTACK:					
					return $this->attack([$id], $player);
					break;
			}
		} elseif($this->game->getGameStateValue('state') == WAIT_REACTION ) {
			$player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $player);
			switch($this->effect['type']) {
				case BASIC_ATTACK:
					if($id == 999) {
						$this->looseLife($player, $player_name, $this->game->getGameStateValue('currentTurn'));
					} else {
						$card = self::getObjectListFromDB("SELECT card_type type,card_name name FROM cards WHERE id=" . $this->id)[0];
						if($card['type'] == 11) {
							self:DbQuery("UPDATE cards SET position=-2 WHERE id=" . $this->id);
							$this->game->setGameStateValue('state',0);
							
							self::notifyAllPlayers('cardPlayed', "$player_name used " . $card->name, array('card' => $card, 'player' => self::getCurrentPlayerId()));
							$this->game->gamestate->nextState( "awaitReaction" );
							
						} else {
							//todo show error
						}
					}
					break;
			}
		}
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
	public function attack($player_ids, $player) {
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
