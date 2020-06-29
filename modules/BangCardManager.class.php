<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */
class BangCardManager extends APP_GameClass
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

  /*
   * cardClasses : for each card Id, the corresponding class name
   */
  public static $classes = [
    CARD_BANG => 'CardBang',
    CARD_GATLING => 'CardGatling',
    CARD_PUNCH => 'CardPunch',
    CARD_SPRINGFIELD => 'CardSpringfield',
    CARD_CANNON => 'CardCannon',
    CARD_INDIANS => 'CardIndians',
    CARD_DUEL => 'CardDuel',
    CARD_MISSED => 'CardMissed',
    CARD_DODGE => 'CardDodge',
    CARD_BEER => 'CardBeer',
    CARD_WHISKY => 'CardWhisky',
    CARD_TEQUILA => 'CardTequila',
    CARD_SALOON => 'CardSaloon',
    CARD_CAT_BALOU => 'CardCatBalou',
    CARD_BRAWL => 'CardBrawl',
    CARD_PANIC => 'CardPanic',
    CARD_RAG_TIME => 'CardRagTime',
    CARD_STAGECOACH => 'CardStagecoach',
    CARD_WELLS_FARGO => 'CardWellsFargo',
    CARD_GENERAL_STORE => 'CardGeneralStore',
  ];



}
