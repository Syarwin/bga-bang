<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */
class BangCardManager extends APP_GameClass
{
	public $game;
	public static $deck = null;
	public function __construct($game)
	{
		$this->game = $game;

/*
		$this->terrains = $this->game->getNew("module.common.deck");
		$this->terrains->init("terrains");
		$this->terrains->autoreshuffle = true;
*/
	}

	public function setupNewGame($expansions)
	{
		self::$deck = self::getNew("module.common.deck");
		self::$deck->init("card");

		$cards = [];
		foreach(self::$classes as $id => $name) {
			$card = new $name();
			foreach($expansions as $exp) {
				foreach($card->copies[$exp] as $value) {
					$cards[] = ['type' => $value, 'type_arg' => $id, 'nbr' => 1];
				}
			}
		}
		self::$deck->createCards($cards, 'deck');
		return count($values);
	}

	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public static function countCards($location, $player=null) {
		if($player==null)
			return self::$deck->countCardsInLocation($location);
		else
			return self::$deck->countCardsInLocation($location, $player);
	}

	

	/**
	  * getHand : Returns the cards of a players hand
	  */
	public static function getHand($id) {
		return self::$deck->getCardsInLocation('hand' $player_id);
	}

	/**
	 * getCardsInPlay : returns all Cards in play
	 */
	public static function getCardsInPlay() {
		return self::$deck->getCardsInLocation('inPlay');
	}

	/**
	 * getEquipment : returns all equipment Cards the players has in play as array: id => cards
	 */
	public static function getEquipment() {
		$cards = [];
		$players = BangPlayerManager::getPlayers();
		foreach($players as $id => $char) {
			$cards[$id] = self::getCardsInLocation('inPlay');
		}
		return $cards;
	}

	/*
	 *
	 */
	public static function getCard($id, $game=null) {
		$card_id = self::$deck->getCard($id);
		$name = self::$classes[$card_id];
		$card = new $name();
		$card->$id = $id;
		if($game != null) $card->game = $game;
		return $card;
	}


	public static function moveCard($id, $location, $arg=0) {
		self::$deck->moveCard($id, $location, $arg);
	}

	/*
	 * cardClasses : for each card Id, the corresponding class name
	 */
	public static $classes = [
		CARD_SCHOFIELD => 'CardSchofield',
		CARD_VOLCANIC => 'CardVolcanic',
		CARD_REMINGTON => 'CardRemington',
		CARD_REV_CARABINE => 'CardRevCarabine',
		CARD_WINCHESTER => 'CardWinchester',
		CARD_BANG => 'CardBang',
		CARD_MISSED => 'CardMissed',
		CARD_STAGECOACH => 'CardStagecoach',
		CARD_WELLS_FARGO => 'CardWellsFargo',
		CARD_BEER => 'CardBeer',
		CARD_GATLING => 'CardGatling',
		CARD_PANIC => 'CardPanic',
		CARD_CAT_BALOU => 'CardCatBalou',
		CARD_DUEL => 'CardDuel',
		CARD_SALOON => 'CardSaloon',
		CARD_GENERAL_STORE => 'CardGeneralStore',
		CARD_INDIANS => 'CardIndians',
		CARD_JAIL => 'CardJail',
		CARD_DYNAMITE => 'CardDynamite',
		CARD_BARREL => 'CardBarrel',
		CARD_SCOPE => 'CardScope',
		CARD_MUSTANG => 'CardMustang'

		/*CARD_PUNCH => 'CardPunch',
		CARD_SPRINGFIELD => 'CardSpringfield',
		CARD_CANNON => 'CardCannon',
		CARD_DODGE => 'CardDodge',
		CARD_WHISKY => 'CardWhisky',
		CARD_TEQUILA => 'CardTequila',
		CARD_BRAWL => 'CardBrawl',
		CARD_RAG_TIME => 'CardRagTime',*/
	];


}
