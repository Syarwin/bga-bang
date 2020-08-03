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
						$player_ids = PlayerManager::getLivingPlayers($player->id);
						$choose = false;
						break;
					case INRANGE:
						$player_ids = $player->getPlayersInRange($player->getRange());
						break;
					case SPECIFIC_RANGE:
						$player_ids = $player->getPlayersInRange($this->effect['range']);
						break;
					case ANY:
						$player_ids = PlayerManager::getLivingPlayers();
						break;
				}
				if($choose) {
					$player->askForTarget($player_ids, $this->id);
					return false;
				} else {
					$player->attack($player_ids);
				}
				break;
			case DRAW:
			case DISCARD:
			case LIFE_POINT_MODIFIER:
				break;
			default:
				return false;
			break;
		}
		return true;
	}
	
	public function react($id, $player) {
		if($this->game->getGameStateValue('state')==PLAY_CARD) {
			switch($this->effect['type']) {
				case BASIC_ATTACK:					
					$player->attack([$id]);
					$name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $player->player);
					$card_name = $this->name;
					$this->game->notifyAllPlayers('cardPlayed', "$name played $card_name.", array('card' => $this->id, 'player' => $player->player));
					return true;
			}
		} elseif($this->game->getGameStateValue('state') == WAIT_REACTION ) {
			$player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $player->id);
			switch($this->effect['type']) {
				case BASIC_ATTACK:
					if($id == 999) {
						$player->looseLife($this->game->getGameStateValue('currentTurn'));
					} else {
						$card = self::getObjectListFromDB("SELECT card_type type,card_name name FROM cards WHERE id=" . $this->id)[0];
						if($card['type'] == 11) {
							self:DbQuery("UPDATE cards SET position=-2 WHERE id=" . $this->id);
							$this->game->setGameStateValue('state',0);
							
							self::notifyAllPlayers('cardPlayed', "$player_name used " . $card->name, array('card' => $card, 'player' => $player->id));
							$this->game->gamestate->nextState( "awaitReaction" );
							
						} else {
							//todo show error
						}
					}
					break;
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function createDebug($args) {
		return ['notifs'=>[['notif'=>'debug', 'msg'=>'', 'args'=>$args]]];
	}
}
