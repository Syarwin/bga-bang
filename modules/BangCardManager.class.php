<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */
class BangCardManager extends APP_GameClass
{
	private static $deck = null;

	private static function getDeck() {
		if(self::$deck==null) {
				self::$deck = self::getNew("module.common.deck");
				self::$deck->init("card");
				self::$deck->autoreshuffle = true;
		}
		return self::$deck;
	}

	public static function setupNewGame($expansions)	{
		$cards = [];
		foreach(self::$classes as $id => $name) {
			$card = new $name();
			foreach($expansions as $exp) {
				foreach($card->getCopies()[$exp] as $value) {
					$cards[] = ['type' => $id, 'type_arg' => $value, 'nbr' => 1];
				}
			}
		}
		self::getDeck()->createCards($cards, 'deck');
		self::getDeck()->shuffle('deck');
	}


	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public static function countCards($location, $player=null) {
		if($player==null)
			return self::getDeck()->countCardsInLocation($location);
		else
			return self::getDeck()->countCardsInLocation($location, $player);
	}

	public static function formatCard($card){
		return $card->format();
	}

	public static function formatCards($cards){
		return array_values(array_map(['BangCardManager', 'formatCard'], $cards));
	}

	/**
	  * getHand : Returns the cards of a players hand
	  */
	public static function getHand($playerId, $formatted = false) {
		$cards = self::toObjects(self::getDeck()->getCardsInLocation('hand', $playerId));
		return $formatted? self::formatCards($cards) : $cards;
	}

	/**
	 * getCardsInPlay : returns all Cards in play
	 */
	public static function getCardsInPlay($playerId = null, $formatted = false) {
		$cards = is_null($playerId)? self::getDeck()->getCardsInLocation('inPlay') : self::getDeck()->getCardsInLocation('inPlay', $playerId);
		$cards = self::toObjects($cards);
		return $formatted? self::formatCards($cards) : $cards;
	}

	/**
	 * getCardsInPlay : returns all Cards in play
	 */
	public static function getLastDiscarded() {
		$card = self::getDeck()->getCardOnTop('discard');
		return is_null($card) ? null : self::resToObject($card)->format();
	}

	/**
	 * getEquipment : returns all equipment Cards the players has in play as array: id => cards
	 */
	public static function getEquipment() {
		$cards = [];
		$bplayers = BangPlayerManager::getPlayers();
		foreach($bplayers as $id => $char) {
			$cards[$id] = self::toObjects(self::getDeck()->getCardsInLocation('inPlay'));
		}
		return $cards;
	}

	public static function toObjects($array) {
		$cards = [];
		foreach($array as $row) $cards[] = self::resToObject($row);
		return $cards;
	}

	/*
	 *
	 */
	public static function getCard($id) {
		$c = self::getDeck()->getCard($id);
		$card_id = $c['type'];
		$name = self::$classes[$card_id];
		$card = new $name($id);
		$card->setCopy($c['type_arg']);
		return $card;
	}

	private static function resToObject($row) {
		$card_id = $row['type'];
		$name = self::$classes[$card_id];
		$card = new $name($row['id']);
		$card->setCopy($row['type_arg']);
		return $card;
	}

	public static function getOwner($id) {
		$card = self::getDeck()->getCard($id);
		return BangPlayerManager::getPlayer($card['location_arg']);
	}

	public static function moveCard($id, $location, $arg=0) {
		self::getDeck()->moveCard($id, $location, $arg);
	}

	public static function playCard($id) {
		self::getDeck()->playCard($id);
	}

	public static function deal($player, $amount){
		return self::toObjects(self::getDeck()->pickCards($amount, 'deck', $player));
	}

  // only for testing
	public static function dealCard($player, $type) {
		//$cards = self::getDeck()->getCardsOfType($type);
		$sql = "SELECT card_id FROM card WHERE card_type=$type";
		$cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_type=$type", true);
		self::getDeck()->moveCard($cards[0], 'hand', $player);
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

	public static function getUiData() {
		$ui = [];
		foreach (self::getAll() as $card) {
			$ui[] = $card->getUiData();
		}
		return $ui;
	}

	/*
	 * getAll: return all type of cards
	 */
	public static function getAll()	{
		return array_map(function ($type){
			return self::getCardByType($type);
		}, array_keys(self::$classes));
	}

	/*
	 * getCardOfType: factory function to create a card given its type
	 */
	public static function getCardByType($cardType)	{
		if (!isset(self::$classes[$cardType])) {
			throw new BgaVisibleSystemException("getCardByType: Unknown card $cardType");
		}
		return new self::$classes[$cardType]();
	}

}
