<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
{

	public function __construct($id=null, $game=null){
		$this->game=$game;
		$this->id=$id;
	}

  protected $type;
	protected $id;
	protected $name;
	protected $text;
	protected $copies = [];
	protected $color;
	protected $effect; // array with type, impact and sometimes range
	protected $game;
	protected $copy;




	public function getId(){ return $this->id; }
	public function getType(){ return $this->type; }
	public function getCopy(){ return $this->copy; }

	// TODO : convert str to int ? (J => 11, Q => 12, K => 13 ?)
	public function getValue(){ return substr($this->copy, 0, -1); }
	// TODO : convert str to php constants
	public function getColor(){ return substr($this->copy, -1); }

	public function getEffectType(){ return $this->effect['type']; }
	public function getName(){ return $this->name; }
	public function getText(){ return $this->text; }
	public function getCopies(){ return $this->copies; }
	public function getEffect(){ return $this->effect; }
	public function isEquipment(){return $this->color == BLUE;}
	public function isAction(){return $this->color == BROWN;}

	public function setCopy($copy) {$this->copy = $copy;}
	//public function isPlayable() { return true; }

	/**
	 * play : default function to play a card that. Can be used for cards that have only symbols
	 */
	public function play($player) {
		//$bplayers = BangPlayerManager::getPlayers();

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
					$name = BangPlayerManager::getPlayer($player->getPlayer())->getName();
					$card_name = $this->getName();
					$this->game->notifyAllPlayers('cardPlayed', "$name played $card_name.", array('card' => $this->id, 'player' => $player->getPlayer()));
					BangCardManager::moveCard($this->id, 'discard');
					return true;
			}
		} elseif($this->game->getGameStateValue('state') == WAIT_REACTION ) {
			$player_name = BangPlayerManager::getPlayer($player->player)->getName();
			switch($this->effect['type']) {
				case BASIC_ATTACK:
					if($id == 999) {
						$player->looseLife($this->game->getGameStateValue('currentTurn'));

					} else {
						$card = BangCardManager::getCard($id);
						if($card['type'] == 11) {
							BangCardManager::moveCard($card->id, 'discard');

							$this->game->setGameStateValue('state',0);

							self::notifyAllPlayers('cardPlayed', "$player_name used " . $card->getName(), array('card' => $card->id, 'player' => $player->id));
							$this->game->gamestate->nextState( "awaitReaction" );

						} else {
							//todo show error
						}
					}
					break;
			}
		}
	}



	public function getUIData() {
		return [
			'type' => $this->type,
			'name' => $this->name,
			'text' => $this->text,
		];
	}





	public function createDebug($args) {
		return ['notifs'=>[['notif'=>'debug', 'msg'=>'', 'args'=>$args]]];
	}
}
