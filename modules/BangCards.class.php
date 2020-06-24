<?php

/*
 * BangCards: all utility functions concerning cards are here
 */
class BangCards extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;

/*
    $this->terrains = $this->game->getNew("module.common.deck");
    $this->terrains->init("terrains");
    $this->terrains->autoreshuffle = true;
*/
  }

  public function setupNewGame($players, $optionSetup)
  {
/*
    // Create terrains cards
    $terrains = [];
    for($i = 0; $i < 5; $i++){
      $terrains[] = ['type' => $i, 'type_arg' => 0, 'nbr' => 5];
    }
    $this->terrains->createCards($terrains, 'deck');
    $this->terrains->shuffle('deck');
*/
  }
}
