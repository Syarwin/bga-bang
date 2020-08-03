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
    foreach ($this->getAll() as $card) {
			foreach($expansions as $expansion){
				if(!array_key_exists($expansion, $card->getCopies()))
					continue;

				foreach($card->getCopies()[$expansion] as $copy){
					$values[] = ['type' => $card->getId(), 'type_arg' => $this->encodeTypeArg($copy), 'nbr' => 1];
				}
			}
    }
    $this->cards->createCards($values, 'deck');
	}

	/**
	 * encode/decode : allow to go from copy in the format '10S' to a int and vice-versa
	 */
	public static function encodeTypeArg($copy)
	{
		$value = ['A' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, 'J' => 11, 'Q' => 12, 'K' => 13];
		$color = ['S' => SPADE, 'H' => HEART, 'D' => DIAMOND, 'C' => CLUB];
		$cardValue = $value[substr($copy, 0, -1)];
		$cardColor = $color[substr($copy, -1)];
		return 14*$cardColor + $cardValue;
	}

	public static function decodeCardValue($typeArg)
	{
		return $typeArg % 14;
	}

	public static function decodeCardColor($typeArg)
	{
		return (int) floor($typeArg / 14);
	}


	/**
	 * formatCard : allow to turn a card from php deck component to something we like better
	 */
	public static function formatCard($card)
	{
		return [
			'id' => $card,
			'type' => $card['type'],
			'color' => self::decodeCardColor((int) $card['type_arg']),
			'value' => self::decodeCardValue((int) $card['type_arg']),
		];
	}

	private function formatCards($cards)
	{
		return array_map(array('BangCardManager','formatCard'), $cards);
	}


	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public function getDeckCount() {
		return count($this->cards->getCardsInLocation('deck'));
	}

	/**
	 * getHand : Returns the cards of a players hand
	 */
	public function getCardsInHand($playerId) {
		return $this->formatCards($this->cards->getCardsInLocation('hand', $playerId));
	}

	/**
	 * getCardsInPlay : returns all cards in play of a player
	 */
	public function getCardsInPlay($playerId) {
		return $this->formatCards($this->cards->getCardsInLocation('inplay', $playerId));
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
    foreach ($this->getAll() as $card) {
      $ui[$card->getId()] = $card->getUiData();
    }
    return $ui;
  }


	/*
   * getAll: return all type of cards
   */
  public function getAll()
  {
    return array_map(function ($type){
      return $this->getCardByType($type, null);
    }, array_keys(self::$classes));
  }



	/*
   * getCardOfType: factory function to create a card given its type
   */
  public function getCardByType($cardType, $playerId = null)
  {
    if (!isset(self::$classes[$cardType])) {
      throw new BgaVisibleSystemException("getCardByType: Unknown card $cardType (player: $playerId)");
    }
    return new self::$classes[$cardType]($this->game, $playerId);
  }


	/*
   * getCard: factory function to create a card by ID
   */
  public function getCard($cardId, $playerId = null)
  {
		$card = $this->cards->getCard($cardId);
    if (is_null($card)) {
      throw new BgaVisibleSystemException("getCard: can't find card $cardId (player: $playerId)");
    }
    return $this->getCardByType($card['type']);
  }



	public function drawCards($n, $playerId)
	{
		$this->cards->pickCards($n, 'deck', $playerId);
	}
}
