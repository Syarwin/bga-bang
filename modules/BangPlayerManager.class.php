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
			$values[] = "($pId, '$color','$canal','$name','$avatar', $bullets, $bullets, $role, $char_id)";
			BangCardManager::deal($pId,$bullets);
		}
		self::DbQuery($sql . implode($values, ','));
		BangCardManager::dealCard($sheriff, CARD_GENERAL_STORE);
		//BangCardManager::dealCard($sheriff, CARD_JAIL, 1);
		bang::$instance->reloadPlayersBasicInfos();
		return $sheriff;
	}


	public static function getCurrentTurn($asObject = false){
		$playerId = BangLog::getPlayerTurn();
		return $asObject? self::getPlayer($playerId) : $playerId;
	}

	public static function getActivePlayer(){
		return self::getPlayer(bang::$instance->getActivePlayerId());
	}

	public static function getSherrifId() {
		return self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_role=" . SHERIFF);
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
	public static function getLivingPlayers($except = null, $asPlayerObjects = false) {
		$sql = "SELECT player_id FROM player WHERE player_eliminated = 0";
		if($except != null){
			$ids = is_array($except)? $except : [$except];
			$sql .= " AND player_id NOT IN ('" . implode("','", $ids) . "')";
		}
		$sql .= " ORDER BY player_no";
		$ids = self::getObjectListFromDB($sql, true);
		return $asPlayerObjects ? self::getPlayers($ids) : $ids;
	}


	  public static function getNextPlayer($id) {
	    $players = self::getLivingPlayers();
			foreach($players as $idx => $pid) {
				if($pid == $id)
					return self::getPlayer($idx==count($players)-1 ? $players[0] : $players[$idx+1]);
			}
	  }



	public static function preparePlayerActivation($playerIds) {
		self::DbQuery("UPDATE player SET player_activate=0");
		$ids = implode(",", $playerIds);
		self::DbQuery("UPDATE player SET player_activate=1 WHERE player_id in($ids)");
	}

	public static function getPlayersForActivation() {
		return self::getObjectListFromDB("SELECT player_id FROM player WHERE player_activate=1", true);
	}

	public static function getTarget() {
		return BangLog::getLastAction("target")["target"];
	}


	/*
	 * getUiData : get all ui data of all players : id, hp, max_hp no, name, color, character, powers(character effect), hand(count)  [if $full then also role]
	 */
	public static function getUiData($currentPlayer)	{
		return array_map(function($player) use ($currentPlayer){
			return $player->getUiData($currentPlayer);
		}, self::getPlayers());
	}

	public static function getCharactersByExpansion($expansions) {
		$characters = [
			BASE_GAME => range(0,15)
			// add new expansions
		];
		return array_reduce($expansions, function($res, $exp) use ($characters){
			return array_merge($res, $characters[$exp]);
		}, []);
	}

	/*
	 * characterClasses : for each character Id, the corresponding class name
	 */
	public static $classes = [
		LUCKY_DUKE => 'LuckyDuke', // todo 2
		EL_GRINGO => 'ElGringo', // tested(with Bang!)
		SID_KETCHUM => 'SidKetchum', // done
		BART_CASSIDY => 'BartCassidy', // done
		JOURDONNAIS => 'Jourdonnais', // done
		PAUL_REGRET => 'PaulRegret', // done
		BLACK_JACK => 'BlackJack', // todo 2
		PEDRO_RAMIREZ => 'PedroRamirez', // todo 2
		SUZY_LAFAYETTE => 'SuzyLafayette', // todo 1
		KIT_CARLSON => 'KitCarlson', // done
		VULTURE_SAM => 'VultureSam', // todo 1
		JESSE_JONES => 'JesseJones', // todo 2
		CALAMITY_JANET => 'CalamityJanet', // tested
		SLAB_THE_KILLER => 'SlabtheKiller', // todo 2
		WILLY_THE_KID => 'WillytheKid', // done
		ROSE_DOOLAN => 'RoseDoolan', // tested

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
