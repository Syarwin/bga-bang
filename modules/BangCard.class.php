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
	protected $symbols;
	protected $color;
	protected $effect; // array with type, impact and sometimes range
	protected $copies = [];
	protected $copy;




	public function getId(){ return $this->id; }
	public function getType(){ return $this->type; }
	public function getName(){ return $this->name; }
	public function getText(){ return $this->text; }
	public function getEffect(){ return $this->effect; }
	public function getSymbols(){ return $this->symbols; }
	public function getColor(){ return $this->color; }
	public function getCopies(){ return $this->copies; }
	public function getCopy(){ return $this->copy; }

	// TODO : convert str to int ? (J => 11, Q => 12, K => 13 ?)
	public function getCopyValue(){ return substr($this->copy, 0, -1); }
	// TODO : convert str to php constants
	public function getCopyColor(){ return substr($this->copy, -1); }

	public function getEffectType(){ return $this->effect['type']; }
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
 				$ids = ($this->effect['impacts'] == ALL_OTHER) ? PlayerManager::getLivingPlayers($player->id) : $targets;
 				$player->attack($ids);
 				break;
 			case DRAW:
				if(count($targets)==0) { //draw from deck
					$card = BangCardManager::deal($player, 1);
					BangNotificationManager::gainedCard($player, $card);
				} else {

					$card = null;
					$victim=null;
					if($target[0]<0) {
						$victim = BangPlayerManager::getPlayer(-intval($target[0]));
						$hand = BangCardManager::getHand($victim->getId());
						shuffle($hand);
						$card = $hand[0];
					} else {
						$victim = BangCardManager::getOwner($targets[0]);
						$card = BangCardManager::getCard($targets[0]);
					}
					BangCardManager::moveCard($card->id, 'hand', $player->getId());
					BangNotificationManager::stoleCard($player, $victim, $card);
				}
				break;
 			case DISCARD:
				$victim = $target[0];
				$card = null;
				if(count($targets)>1) {
					$card = BangCardManager::getCard($targets)[1];
				} else {
					$hand = BangCardManager::getHand($victim);
					shuffle($hand);
					$card = $hand[0];
				}
				BangCardManager::moveCard($card->id, 'discard');
				BangNotificationManager::discardedCards($victim, [$card]);
				break;
 			case LIFE_POINT_MODIFIER:
				$target = count($targets)>0 ? BangPlayerManager::getPlayer($targets[0]) : $player;
				$hp = $target->getHp();
				$bullets = $target->getBullets();
				$amount = $this->effect['amount'];
				if($hp+$amount > $bullets) $amount = $bullets - $hp;
				$target->setHp($hp+$amount);
				$target->save();
				BangNotificationManager::gainedLife($player, $amount);
 				break;
 			default:
 				return false;
 			break;
 		}
 		return true;
	}

	public function react($id, $player) {
		$player_name = BangPlayerManager::getPlayer($player->player)->getName();
		switch($this->effect['type']) {
			case BASIC_ATTACK:
				if($id == PASS) {
					$player->looseLife(bang::$instance->getGameStateValue('currentTurn'));
				} else {
					$card = BangCardManager::getCard($id);
					BangCardManager::moveCard($card->id, 'discard');
					bang::$instance->gamestate->nextState( "awaitReaction" );
				}
				break;
		}
		return true;
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
			'text' => $this->text
		];
	}

	public function format() {
		return [
			'id' => $this->id,
			'type' => $this->type,
			'color' => substr($this->copy, -1),
			'value' => substr($this->copy, 0, -1),
		];
	}

  public function getPlayOptions($player) {
	 switch ($this->color) {
	 	case BLUE:
	 		$cardsInPlay = BangCardManager::getCardsInPlay($player->getId());
			return ['type' => OPTIONS_NONE];
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
					$type = /*($this->effect['impacts'] == ALL_OTHER) ? OPTION_CARDS :*/ OPTION_CARD;
					break;
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
				case NONE:
					$player_ids = [];
					break;
			}
			$deck = ($type == OPTION_CARD && $this->effect['type'] == DRAW);
			if($this->getEffectType() == LIFE_POINT_MODIFIER) {
				$players = BangPlayerManager::getPlayers($player_ids);
				$filtered_ids = [];
				foreach($players as $p)
					if($p->getHp() < $p->getBullets()) $filtered_ids[] = $p->getId();
				if(count($filtered_ids) == 0) return null;
				$player_ids = $filtered_ids;
			}
			return [
				'type' => $type,
				'targets' => array_values($player_ids),
				'deck' => $deck
			];

	 		break;
	 }
 }


}
