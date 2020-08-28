<?php

class CardBang extends BangBrownCard {
  public function __construct($id = null, $copy = ""){
    parent::__construct($id, $copy);
    $this->type  = CARD_BANG;
    $this->name  = clienttranslate('BANG!');
    $this->text  = clienttranslate("A Bang to a player in range. Can usually only be played once per turn");
    $this->symbols = [
      [SYMBOL_BANG, SYMBOL_INRANGE]
    ];
    $this->copies = [
      BASE_GAME => [ 'AS', '8D', '9D', '10D', 'JD', 'QD', 'KD', 'AD', '2C', '3C', 'QH', 'KH', 'AH', '2D', '3D', '4D', '5D', '6D', '7D', '4C', '5C', '6C', '7C', '8C', '9C' ],
      DODGE_CITY => [ '8S', '5C', '6C', 'KC'],
    ];
    $this->effect = [
      'type' => BASIC_ATTACK,
			'range' => 0,
			'impacts' => INRANGE
		];
  }

  /*
   * Only one bang per turn, unless unlimitedBangs granted by Volcanic or by character
   */
  public function getPlayOptions($player) {
    if($player->hasUnlimitedBangs() || !$player->hasPlayedBang()) {
      return parent::getPlayOptions($player);
    }
    return null;
  }

  public function play($player, $args) {
    BangLog::addAction("bangPlayed");
    return parent::play($player, $args);
  }
  public function getReactionOptions($player) {
    $options = parent::getReactionOptions($player);
    $args = BangLog::getLastAction('cardPlayed');

    $options['amount'] = isset($args['missedNeeded']) ? $args['missedNeeded'] : 1;
    return $options;
  }


}
