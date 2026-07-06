
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
			$cards[$id] = self::toObjects(self::getDeck()->getCardsInLocation(LOCATION_INPLAY, $id));
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


