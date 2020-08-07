<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
{

	public function __construct($id=null){
		$this->id=$id;
	}

	protected $id;
  protected $type;
	protected $name;
	protected $text;
	protected $color;
	protected $effect; // array with type, impact and sometimes range
	protected $copies = [];
	protected $copy;




	public function getId(){ return $this->id; }
	public function getType(){ return $this->type; }
	public function getCopy(){ return $this->copy; }

	// TODO : convert str to int ? (J => 11, Q => 12, K => 13 ?)
	public function getCopyValue(){ return substr($this->copy, 0, -1); }
	// TODO : convert str to php constants
	public function getCopyColor(){ return substr($this->copy, -1); }

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
	public function play($player, $targets) {
 		switch ($this->effect['type']) {
 			case BASIC_ATTACK:
 				$ids = ($this->effect['impacts'] == ALL_OTHER) ? PlayerManager::getLivingPlayers($player->id): targets;
 				$player->attack(ids);

 				break;
 			case DRAW:
 			case DISCARD:
 			case LIFE_POINT_MODIFIER:
 				break;
 			default:
 				return false;
 			break;
 		}
		BangNotificationManager::cardPlayed($this, $card, $targets);
 		return true;
	}

	public function react($id, $player) {
		$player_name = BangPlayerManager::getPlayer($player->player)->getName();
		switch($this->effect['type']) {
			case BASIC_ATTACK:
				if($id == 999) {
					$player->looseLife(bang::$instance->getGameStateValue('currentTurn'));
				} else {
					$card = BangCardManager::getCard($id);
					BangCardManager::moveCard($card->id, 'discard');
					bang::$instance->setGameStateValue('state',0);
					BangNotificationManager::cardPlayed($card, $player);
					bang::$instance->gamestate->nextState( "awaitReaction" );
				}
				break;
		}
	}

  /**
	 * can be overwritten to add an additional Message to the played card notification.
	 * this message should start with a space
	 */
	public function getArgsMessage($args) {return "";}

	public function getUIData() {
		return [
			'type' => $this->type,
			'name' => $this->name,
			'text' => $this->text,
			'effect' => $this->effect
		];
	}

  public function getPlayOptions($player) {
	 switch ($this->color) {
	 	case BLUE:
	 		$cardsInPlay = BangCardManager::getCardsInPlay($player->getId());
			return ['type' => OPTIONS_NONE, '$cardsInPlay' => BangCardManager::formatCards($cardsInPlay), 'type' => 'type'];
			/*foreach($cardsInPlay as $card)
				if($card->type == $this->type)
					return null;
			return ['type' => OPTIONS_NONE];*/
	 	case BROWN:
			$type = -1;
			switch ($this->effect['type']) {
				case BASIC_ATTACK:
				case LIFE_POINT_MODIFIER:
					if ($this->effect['impacts'] == ALL || $this->effect['impacts'] == ALL_OTHER) {
						return ['type' => OPTIONS_NONE];
					}
					$type = OPTION_PLAYER;
					break;
				case DRAW:
				case DISCARD:
					$type = ($this->effect['impacts'] == ALL_OTHER) ? OPTION_CARDS : OPTION_CARD;
				case DEFENSIVE:
					return null;
				default:
					return ['type' => OPTIONS_NONE];
				break;
			}
			$player_ids = [];
			switch($this->effect['impacts']) {
				case ALL_OTHER:
					$player_ids = PlayerManager::getLivingPlayers($player->id);
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
			$deck = ($type == OPTION_CARD && $this->effect['type'] == DRAW);
			return ['type' => $type, 'targets' => array_values($player_ids), 'deck'=>$deck];

	 		break;
	 }
 }


}
