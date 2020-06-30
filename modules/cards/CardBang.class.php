<?php

class CardBang extends BangCard {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CARD_BANG;
    $this->name  = clienttranslate('BANG!');
    $this->text  = [ ];
    $this->type = BROWN; //BROWN, BLUE, GREEN
    $this->effects = [BASIC_ATTACK]; // BASIC_ATTACK, DRAW, DEFENSIVE, STEAL, DISCARD, SPECIAL_ATTACK, LIFE_POINT_MODIFIER, GUN, OFF_SIGHT_MODIFIER, DEF_SIGHT_MODIFIER, PERMA_DEFENSIVE, TURN_SKIPPER, RANDOM_DAMAGE
    $this->conditions = ['oneOnTurn'];

    $this->range = REACHABLE_DISTANCE; // NULL, ONE, REACHABLE, ANY
    $this->impacts = ONE_OTHER; // SELF, ONE, ONE_OTHER, ALL_OTHER, ALL
    
    $this->copies = [
      BASE_GAME => [ 'AS', 'QH', 'KH', 'AH', '2D', '3D', '4D', '5D', '6D', '7D', '8D', '9D', '10D', 'JD', 'QD', 'KD', 'AD', '2C', '3C', '4C', '5C', '6C', '7C', '8C', '9C' ],
      DODGE_CITY => [ '8S', '5C', '6C', 'KC'],
    ];
  }
}
