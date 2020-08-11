<?php

/*
 * BangPlayerManager: all utility functions concerning players
 */

require_once('BangPlayer.class.php');

class BangPlayerManager extends APP_GameClass
{

	public static function setupNewGame($bplayers, $expansions)	{
		self::DbQuery('DELETE FROM player');
		$gameInfos = bang::$instance->getGameinfos();
		$sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_bullets, player_score, player_role, player_character) VALUES ';

		$roles = array_slice(array(0,2,2,3,1,2,1),0,count($bplayers));
		shuffle($roles);

		$characters = self::getCharactersByExpansion($expansions);
		shuffle($characters);

		$values = [];
		$i = 0;
		foreach ($bplayers as $pId => $player) {
			$color = $gameInfos['player_colors'][$i];
			$canal = $player['player_canal'];
			$name = $player['player_name'];
			$avatar = addslashes($player['player_avatar']);
			$name = addslashes($player['player_name']);
			$role = $roles[$i];
			$char_id = $characters[$i++];
			$char  = new self::$classes[$char_id]();
			$bullets = $char->getBullets();
			if($role == SHERIFF) {
				$bullets++;
				$sheriff = $pId;
			}
			$values[] = "($pId, '$color','$canal','$name','$avatar', $bullets, $bullets-1, $role, $char_id)";
			BangCardManager::deal($pId,$bullets);
		}
		self::DbQuery($sql . implode($values, ','));
		BangCardManager::dealCard($sheriff, CARD_STAGECOACH);
		bang::$instance->reloadPlayersBasicInfos();
		return $sheriff;
	}



	/*
	 * getPlayer : returns the BangPlayer object for the given player ID
	 */
	public static function getPlayer($playerId)	{
		$bplayers = self::getPlayers([$playerId]);
		return $bplayers[0];
	}

	/*
	 * getPlayers : Returns array of SantoriniPlayer objects for all/specified player IDs
	 * if $asArrayCollection is set to true it return the result as a map $id=>array
	 */
	public static function getPlayers($playerIds = null, $asArrayCollection = false) {
		$columns = ["id", "no", "name", "color", "eliminated", "score", "zombie", "role", "character", "bullets"];
		$sqlcolumns = [];
		foreach($columns as $col) $sqlcolumns[] = "player_$col";
		$sql = "SELECT " . implode(", ", $sqlcolumns) . " FROM player" ;
		if (is_array($playerIds)) {
			$sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
		}

		if($asArrayCollection) return self::getCollectionFromDB($sql);
		$rows = self::getObjectListFromDB($sql);

		$bplayers = [];
		foreach ($rows as $row) {
			$bplayers[] = new self::$classes[$row['player_character']]($row);
		}
		return $bplayers;
	}

  /*
	 * returns an array with positions of all living players . the values won't always be the same as player_no, but a complete sequence [0,#numberOflivingplayers)
	 */
	public static function getPlayerPositions() {
		return array_flip(self::getObjectListFromDB("SELECT player_id from player WHERE player_eliminated=0 ORDER BY player_no", true));
	}

	/**
	 * returns an array of the ids of all living players
	 */
	public static function getLivingPlayers($exept = null) {
		$sql = "SELECT player_id FROM player WHERE player_eliminated=0";
		if($exept != null) $sql.= " AND player_id != $exept";
		return self::getObjectListFromDB($sql, true);
	}

	public static function preparePlayerActivation($playerIds) {
		self::DbQuery("UPDATE player SET player_activate=0");
		$ids = implode(",", $playerIds);
		self::DbQuery("UPDATE player SET player_activate=1 WHERE player_id in($ids)");
	}

	public static function getPlayersForActivation() {
		return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_activate=1", true);
	}

	/*
	 * getUiData : get all ui data of all players : id, hp, max_hp no, name, color, character, powers(character effect), hand(count)  [if $full then also role]
	 */
	public static function getUiData($playerIds = null, $currentPlayer)	{
		$bplayers = self::getPlayers();
		$uidata = [];
		foreach ($bplayers as $player) {
			$uidata[] = $player->getUiData($currentPlayer);
		}
		return array_values($uidata);
	}

	public static function getCharactersByExpansion($expansions) {
		$characters = [
			BASE_GAME => range(0,15)
			// add new expansions
		];
		$res = [];
		foreach($expansions as $exp) $res = array_merge($characters[$exp],$res);
		return $res;
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

}
