
	public static function getCurrentCard(){
		return self::getCard(Log::getCurrentCard());
	}






	/**
	 * getEquipment : returns all equipment Cards the players has in play as array: id => cards
	 */
	public static function getEquipment() {
		$cards = [];
		$bplayers = Players::getPlayers();
		foreach($bplayers as $player) {
			$id = $player->getId();
			$cards[$id] = self::toObjects(self::getDeck()->getCardsInLocation('inPlay', $id));
		}
		return $cards;
	}


	public static function getOwner($id) {
		$card = self::getDeck()->getCard($id);
		return Players::getPlayer($card['location_arg']);
	}

	public static function moveCard($mixed, $location, $arg = 0) {
		$id = ($mixed instanceof Card)? $mixed->getId() : $mixed;
		self::getDeck()->moveCard($id, $location, $arg);
	}

	public static function putOnDeck($card) {
		self::getDeck()->insertCardOnExtremePosition($card, 'deck', true);
	}


	public static function dealFromDiscard($player, $amount){
		return self::deal($player, $amount, "discard");
	}


  public static function reshuffle(){
    self::getDeck()->moveAllCardsInLocation('discard', 'deck');
    self::getDeck()->shuffle('deck');
    Notifications::reshuffle();
  }


	public static function draw() {
    if(self::getDeckCount() == 0){
      self::reshuffle();
    }
		$card = self::resToObject(self::getDeck()->getCardOnTop('deck'));
		self::playCard($card->getId());
		return $card;
	}


	public static function wasPlayed($id) {
		return self::getUniqueValueFromDB("SELECT card_played FROM card WHERE card_id = $id") == 1;
	}

	public static function markAsPlayed($id) {
		self::DbQuery("UPDATE card SET card_played = 1 WHERE card_id=$id");
	}

	public static function resetPlayedColumn() {
		self::DbQuery("UPDATE card SET card_played = 0");
	}
