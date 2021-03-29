<?php
namespace BANG\Cards;
use BANG\Managers\Players;
use BANG\Core\Log;

class Duel extends \BANG\Models\BrownCard{
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_DUEL;
    $this->name  = clienttranslate('Duel');
    $this->text  = clienttranslate("A target player discards a BANG! then you, etc. First player failing to discard a BANG! looses 1 life point.");
    $this->color = BROWN;
    $this->symbols = [
      [$this->text]
    ];
    $this->copies = [
      BASE_GAME => [ 'QD', 'JS', '8C'],
      DODGE_CITY => [ ],
    ];
    $this->effect = [
      'type' => OTHER,
			'range' => 0,
			'impacts' => ANY
		];
  }


  /*
   *
   */
  public function getPlayOptions($player) {
 		$livings = Players::getLivingPlayers($player->getId());
 		return [
 			'type' => OPTION_PLAYER,
 			'targets' => $livings->getIds()
 		];
  }

  public function play($player, $args) {
    $this->discard();
    Log::addAction("duel", ['opponent' => $args['player'] ]);
    return $player->attack([$args['player']], NO_CHECK_BARREL);
  }


  public function getReactionOptions($player) {
    return $player->getBangCards();
  }

  public function getOpponent($player){
    $p1Id = Players::getCurrentTurn();
    $p2Id = Log::getLastAction("duel")["opponent"];
    return $player->getId() == $p1Id? $p2Id : $p1Id;
  }

  public function pass($player){
    parent::pass($player);
    return $player->looseLife();
  }

  public function react($card, $player) {
    $pId = $this->getOpponent($player);
    $player->discardCard($card);
    return $player->attack([$pId], NO_CHECK_BARREL);
  }
}
