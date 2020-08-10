<?php

class ElGringo extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = EL_GRINGO;
    $this->character_name = clienttranslate('El Gringo');
    $this->text  = [
      clienttranslate("Each time he loses a life point due to a card played by another player, he draws a random card from the hands of that player "),

    ];
    $this->bullets = 3;
    parent::__construct($row);
  }

  public function looseLife($byPlayer=-1) {
		parent::looseLife($byPlayer);
		$id = $this->id;
		if($byPlayer > -1) {
			$player = self::getObjectListFromDB("SELECT player_id FROM player", true);
			$cards = self::getObjectListFromDB("SELECT card_id id, card_name name FROM cards WHERE card_position=$byPlayer AND card_onHand=1");

			$n = rand(0,count($cards)-1);
			$card = $cards[$n];

			$hands = self::getCollectionFromDB("SELECT card_position, COUNT(*) FROM cards WHERE card_position>0 GROUP BY card_position", true);
			$name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id=" . $this->id);
			self::DbQuery("UPDATE cards SET card_position=$id WHERE card_id=" . $card['id']);
			$bplayers = BangPlayerManager::getPlayers();
			foreach($bplayers as $player) {
				$pid = $player->getId();
				if($pid==$id) {
					$hand = array_values(BangCardManager::getHand($pid, true));
					bang::$instance->notifyPlayer($pid, 'handChange', "you steal a card from your attacker",
									['hands'=>$hands, 'hand'=>$hand, 'card' => $card, 'gain'=>$id, 'loose'=>$byPlayer]);
				} elseif($pid==$byPlayer) {
					$hand = array_values(BangCardManager::getHand($pid, true));
					bang::$instance->notifyPlayer($pid, 'handChange', "$name steals a card from you",
									['hands'=>$hands, 'hand'=>$hand, 'card' => $card, 'gain'=>$id, 'loose'=>$byPlayer]);
				} else {
					bang::$instance->notifyPlayer($pid, 'handChange', "$name steals a card from his attacker",
									['hands'=>$hands, 'gain'=>$id, 'loose'=>$byPlayer]);
				}
			}
		}
	}
}
