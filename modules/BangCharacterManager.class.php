<?php

/*
 * BangCharacterManager: all utility functions concerning cards are here
 */
class BangCharacterManager extends APP_GameClass
{
	public $game;
	public function __construct($game)
	{
		$this->game = $game;

		$this->characters = $this->game->getNew("module.common.deck");
		$this->characters->init("characters");
	}


	public function setupNewGame($expansions)
	{
    $values = [];
    foreach (array_keys(self::$classes) as $cardId) {
      $values[] = ['type' => $cardId, 'type_arg' => '0', 'nbr' => 1];
    }
    $this->characters->createCards($values, 'deck');
	}


	/*
	 * characterClasses : for each character Id, the corresponding class name
	 */
	public static $classes = [
		LUCKY_DUKE => 'LuckyDuke',
		EL_GRINGO => 'ElGringo',
		SID_KETCHUM => 'SidKetchum',
		BART_CASSIDY => 'BartCassidy',
		JOURDONNAIS => 'Jourdonnais',
		PAUL_REGRET => 'PaulRegret',
		BLACK_JACK => 'BlackJack',
		PEDRO_RAMIREZ => 'PedroRamirez',
		SUZY_LAFAYETTE => 'SuzyLafayette',
		KIT_CARLSON => 'KitCarlson',
		VULTURE_SAM => 'VultureSam',
		JESSE_JONES => 'JesseJones',
		CALAMITY_JANET => 'CalamityJanet',
		SLAB_THE_KILLER => 'SlabtheKiller',
		WILLY_THE_KID => 'WillytheKid',
		ROSE_DOOLAN => 'RoseDoolan',

		/*MOLLY_STARK => 'MollyStark',
		APACHE_KID => 'ApacheKid',
		ELENA_FUENTE => 'ElenaFuente',
		TEQUILA_JOE => 'TequilaJoe',
		VERA_CUSTER => 'VeraCuster',
		BILL_NOFACE => 'BillNoface',
		HERB_HUNTER => 'HerbHunter',
		PIXIE_PETE => 'PixiePete',
		SEAN_MALLORY => 'SeanMallory',
		PAT_BRENNAN => 'PatBrennan',
		JOSE_DELGADO => 'JoseDelgado',
		CHUCK_WENGAM => 'ChuckWengam',
		BELLE_STAR => 'BelleStar',
		DOC_HOLYDAY => 'DocHolyday',
		GREG_DIGGER => 'GregDigger',*/
	];

	/*
   * getUiData : get all ui data of all characters
   */
  public function getUiData()
  {
    $ui = [];
    foreach ($this->getAll() as $character) {
      $ui[] = $character->getUiData();
    }
    return $ui;
  }


	/*
   * getAll: return all characters (even those not available in this game)
   */
  public function getAll()
  {
    return array_map(function ($id){
      return $this->getCharacter($id, null);
    }, array_keys(self::$classes));
  }


	/*
   * getCharacter: factory function to create a character by ID
   */
  public function getCharacter($characterId, $playerId = null)
  {
    if (!isset(self::$classes[$characterId])) {
      throw new BgaVisibleSystemException("getPower: Unknown character $characterId (player: $playerId)");
    }
    return new self::$classes[$characterId]($this->game, $playerId);
  }


	/*
   * getCharacterOfPlayer: return a BangCharacter object of given player
   */
	public function getCharacterOfPlayer($playerId)
  {
		$cards = array_values($this->characters->getCardsInLocation('hand', $playerId));
		if(count($cards) != 1){
			throw new BgaVisibleSystemException("getCharacterOfPlayer : player $playerId don't have a single character associated");
		}
		return $this->getCharacter($cards[0]['type'], $playerId);
	}


	/*
	 * drawCharacter : draw a character card for a player
	 */
	public function drawCharacter($playerId)
	{
		$this->characters->pickCard('deck', $playerId);
	}
}
