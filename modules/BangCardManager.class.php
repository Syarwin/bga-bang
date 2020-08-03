<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */
class BangCardManager extends APP_GameClass
{
	public $game;
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
		$sql = 'INSERT INTO cards(card_id, card_type, card_name, card_text, card_value, card_position, card_onHand) VALUES';
		$values = array();
		foreach(self::$classes as $id => $name) {
			$card = new $name();
			foreach($expansions as $exp) {
				foreach($card->copies[$exp] as $value) {
					$text = str_replace("'","''",$card->text);
					$values[] = "('" . implode("','", [$id, $card->type, $card->name, $text, $value, -1, 0]) . "')";
				}
			}
		}
		$sql .= implode(",",$values);
		self::DbQuery($sql);
		return count($values);
	}
	
	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public static function getDeckCount() {
		return self::getUniqueValueFromDB( "SELECT COUNT(*) FROM cards WHERE card_position=-1" );
	}
	
	/**
	  * getHand : Returns the cards of a players hand as array containing card_id, card_type, card_name, card_text
	  */
	public static function getHand($id) {
		return self::getObjectListFromDB("SELECT id, card_type, card_name, card_text FROM cards WHERE card_position=$id AND card_onHand=1");
	}
	
	/**
	 * getCardsInPlay : returns all Cards in play as array containing card_type, card_name, card_text, card_position(e.g. the player they belong to)
	 */
	public static function getCardsInPlay() {
		self::getObjectListFromDB("SELECT card_type, card_name, card_text, card_position FROM cards WHERE card_position>0 AND card_onHand=0");
	}
	
	/**
	 * getEquipment : returns all equipment Cards a player has in play
	 */
	public static function getEquipment() {
		$res = self::getDoubleKeyCollectionFromDb("SELECT card_position, id, card_id FROM cards WHERE card_position>0 AND card_onHand=0", true);
		$cards = array();
		foreach($res as $pid=>$arr) {
			$cards[$pid] = array();
			foreach($arr as $id=>$card_id) $cards[$pid][] = new $classes[$card_id]();
		}
		return $cards;
	}
	
	/*
	 *
	 */
	public static function getCard($id, $game=null) {
		$card_id = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE id=$id");
		$name = self::$classes[$card_id];
		$card = new $name();
		$card->$id = $id;
		if($game != null) $card->game = $game;
		return $card;
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
