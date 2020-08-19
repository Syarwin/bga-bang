	<?php

/*
 * BangCard: base class to handle characters
 */
class BangCard extends APP_GameClass
{
	public function __construct($id = null, $copy = "")
	{
		$this->id = $id;
		$this->copy = $copy;
	}

	/*
	 * Attributes
	 */
	protected $id;
  protected $type;
	protected $name;
	protected $text;
	protected $symbols;
	protected $effect; // array with type, impact and sometimes range
	protected $copies = [];
	protected $copy;

	/*
	 * getUiData: used in frontend to display cards
	 */
	public function getUIData() {
		return [
			'type' => $this->type,
			'name' => $this->name,
			'text' => $this->text
		];
	}

	/*
	 * format: used in frontend to manipulate cards
	 */
	public function format() {
		return [
			'id' => $this->id,
			'type' => $this->type,
			'color' => $this->getCopyColor(),
			'value' => $this->getCopyValue(),
		];
	}


	/*
	 * Getters
	 */
	public function getId()			{ return $this->id; }
	public function getType()		{ return $this->type; }
	public function getName()		{ return $this->name; }
	public function getText()		{ return $this->text; }
	public function getEffect()	{ return $this->effect; }
	public function getSymbols(){ return $this->symbols; }
	public function getCopies()	{ return $this->copies; }
	public function getCopy()		{ return $this->copy; }

	public function getColor()	{ return null; } // Will be overwrite by Blue/Brown class
	public function getCopyValue() { return substr($this->copy, 0, -1); }
	public function getCopyColor() { return substr($this->copy, -1); }
	public function getEffectType(){ return $this->effect['type']; }

	public function isEquipment(){ return false; }
	public function isAction()	 { return false; }


	public function wasPlayed()	{ return BangCardManager::wasPlayed($this->id);	}


	/**
	 * getPlayOption : default function to know which card can be played by $player
	 * return: type of option and targets if any
	 */
  public function getPlayOptions($player) {
		return [];
	}

	/**
	 * play : default function to play a card that. Can be used for cards that have only symbols
	 * return: null if the game should continue the play loop, "stateName" if another state need to be called
	 */
	public function play($player, $args) { }


	/**
	 * getReactionOptions: default function to handle possible reaction (attack => defense)
	 * return: list of options (cards/abilities) that can be used
	 */
	public function getReactionOptions($player) {
		return $player->getDefensiveOptions();
	}

	/**
	 * react: default function to handle reaction using a card
	 */
	public function react($card, $player) {
		if($this->effect['type'] == BASIC_ATTACK){
			if($card->getColor() == BROWN) {
				BangNotificationManager::cardPlayed($player, $card);
				return BangCardManager::playCard($card->id);
			} else {
				// TODO : notification to highlight the card
				return $card->activate($player);
			}
		}

		return null;
	}

	/**
	 * pass: default function to handle reaction by clicking "pass" button
	 */
	public function pass($player) {
		if($this->effect['type'] == BASIC_ATTACK)
			$player->looseLife();

		return null;
	}


	/**
	 * function to overwrite by blue cards like barrel, jail, dynamite
	 */
	public function activate($player, $args = []) { return null; }


  /**
	 * can be overwritten to add an additional Message to the played card notification.
	 * this message should start with a space
	 */
	 // TODO : not translatable
	public function getArgsMessage($args) {
		if(isset($args['player']) && !is_null($args['player']) ) {
			$name = BangPlayerManager::getPlayer($args['player'])->getName();
			return " and chooses $name as target";
		}
		return "";
	}
}
