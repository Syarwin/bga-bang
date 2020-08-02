<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */

/*
----------- type -------------------------
-- 1x: action
-- 2x: Equipment
-- 30: weapon
-- 10: bang
-- x1: evade
-- x2: rest
-- 99: character
----------- position ---------------------
--  >0: player id
-- -1: deck
-- -2: discard
-- -3: active
----------- value ---------------------
-- xC: Clovers
-- xS: Pikes
-- xD: Spades
-- xH: Hearts
CREATE TABLE IF NOT EXISTS `cards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int NOT NULL,
  `card_type` int NOT NULL,
  `card_name` text NOT NULL,
  `card_text` text NOT NULL,
  `card_value` text NOT NULL,
  `card_position` int NOT NULL,
  `card_onHand` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
*/
class BangCardManager extends APP_GameClass
{
	public $game;
	public function __construct($game)
	{
		$this->game = $game;

		$this->cards = $this->game->getNew("module.common.deck");
		$this->cards->init("cards");
		$this->cards->autoreshuffle = true;
	}


	public function setupNewGame($expansions)
	{
//		$deck = range(1,$n);
//		shuffle($deck);
//		$deck = [8,33,7,9,34,1,10,35,2,11,36,3,12,37,4,13,38,5,14,39,6,15,40];


    $values = [];
    foreach (array_keys(self::$classes) as $cardId) {
			// TODO : several copies of each card
      $values[] = ['type' => $cardId, 'type_arg' => '0', 'nbr' => 1];
    }
    $this->cards->createCards($values, 'deck');
	}

	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public static function getDeckCount() {
		return count($this->cards->getCardsInLocation('deck'));
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


	/*
   * getUiData : get all ui data of all powers : id, name, title, text, hero
   */
  public function getUiData()
  {
    $ui = [];
    foreach ($this->getAkk() as $card) {
      $ui[$card->getId()] = $card->getUiData();
    }
    return $ui;
  }


	/*
   * getAll: return all characters (even those not available in this game)
   */
  public function getAll()
  {
    return array_map(function ($id){
      return $this->getCard($id, null);
    }, array_keys(self::$classes));
  }



	/*
   * getCard: factory function to create a card by ID
	 *	TODO: handle color/value
   */
  public function getCard($cardId, $playerId = null)
  {
    if (!isset(self::$classes[$cardId])) {
      throw new BgaVisibleSystemException("getCard: Unknown card $cardId (player: $playerId)");
    }
    return new self::$classes[$cardId]($this->game, $playerId);
  }


/*
???
	public function createCard($id) {
		$card_id = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE id=$id");
		return new self::$classes[$card_id]();
	}
*/
}
