<?php

// See Dokumentation.pptx for a flowchart about how the state-cycles

$machinestates = [
  /*
   * BGA framework initial state. Do not modify.
   */
  ST_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => [
      '' => ST_START_OF_TURN,
    ],
  ],

  /*
   * Start of a turn : trigger cards such as Dynamite or Jail before moving on to appropriate state
   */
  ST_START_OF_TURN => [
    'name' => 'startOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stStartOfTurn',
    'transitions' => [
      '' => ST_RESOLVE_STACK,
    ],
  ],

  /*
   * Where the flow is resolved
   */
  ST_RESOLVE_STACK => [
    'name' => 'resolveStack',
    'description' => '',
    'type' => 'game',
    'action' => 'stResolveStack',
    'transitions' => [],
  ],

  ST_DRAW_CARDS => [
    'name' => 'drawCards',
    'description' => '',
    'type' => 'game',
    'action' => 'stDrawCards',
    'transitions' => [
      'play' => ST_PLAY_CARD,
      'selection' => ST_PREPARE_SELECTION,
      'activeDraw' => ST_ACTIVE_DRAW_CARD,
    ],
  ],

  ST_ACTIVE_DRAW_CARD => [
    'name' => 'drawCard',
    'description' => clienttranslate('${actplayer} must choose where to draw the first card from'),
    'descriptionmyturn' => clienttranslate('${you} must choose where to draw the first card from'),
    'type' => 'activeplayer',
    'args' => 'argDrawCard',
    'possibleactions' => ['draw'],
    'transitions' => [
      'zombiePass' => ST_END_REACT,
      'play' => ST_PLAY_CARD,
    ],
  ],

  ST_PLAY_CARD => [
    'name' => 'playCard',
    'description' => clienttranslate('${actplayer} can play a card'),
    'descriptionmyturn' => clienttranslate('${you} can play a card'),
    'type' => 'activeplayer',
    'args' => 'argPlayCards',
    'action' => 'stPlayCard',
    'possibleactions' => ['play', 'useAbility', 'endTurn'],
    'transitions' => [
      'zombiePass' => ST_END_REACT,
      'endTurn' => ST_END_OF_TURN,
      'discardExcess' => ST_DISCARD_EXCESS,
      'react' => ST_AWAIT_REACTION,
      'selection' => ST_PREPARE_SELECTION,
      'continuePlaying' => ST_PLAY_CARD,
      'eliminate' => ST_ELIMINATE,
    ],
  ],

  ST_AWAIT_REACTION => [
    'name' => 'awaitReaction',
    'description' => '',
    'type' => 'game',
    'action' => 'stAwaitReaction',
    'updateGameProgression' => true,
    'transitions' => [
      'single' => ST_REACT,
    ],
  ],

  ST_REACT => [
    'name' => 'react',
    'description' => clienttranslate('${actplayer} must react'),
    'descriptionmyturn' => clienttranslate('${you} must react'),
    'type' => 'activeplayer',
    'args' => 'argReact',
    'possibleactions' => ['play', 'pass'],
    'transitions' => [
      'zombiePass' => ST_END_REACT,
      'react' => ST_AWAIT_REACTION,
      'finishedReaction' => ST_END_REACT,
      'eliminate' => ST_ELIMINATE,
    ],
  ],

  ST_PREPARE_SELECTION => [
    'name' => 'prepareSelection',
    'description' => '',
    'type' => 'game',
    'action' => 'stPrepareSelection',
    'updateGameProgression' => true,
    'transitions' => ['select' => ST_SELECT_CARD, 'finish' => ST_PLAY_CARD],
  ],

  ST_SELECT_CARD => [
    'name' => 'selectCard',
    'description' => clienttranslate('${actplayer} must select ${amountToPick} cards for the effect of ${src}'),
    'descriptionmyturn' => clienttranslate('${you} must select ${amountToPick} cards for the effect of ${src}'),
    'descriptionsingle' => clienttranslate('${actplayer} must select a card for the effect of ${src}'),
    'descriptionsinglemyturn' => clienttranslate('${you} must select a card for the effect of ${src}'),
    'type' => 'activeplayer',
    'args' => 'argSelect',
    'possibleactions' => ['select'],
    'transitions' => [
      'zombiePass' => ST_END_REACT,
      'select' => ST_PREPARE_SELECTION,
      'play' => ST_PLAY_CARD, // Needed for KitCarlson
      'skip' => ST_NEXT_PLAYER,
      'draw' => ST_DRAW_CARDS,
    ],
  ],

  ST_END_REACT => [
    'name' => 'endReaction',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndReaction',
    'updateGameProgression' => true,
    'transitions' => [
      'finishedReaction' => ST_PLAY_CARD,
      'eliminate' => ST_ELIMINATE,
      'draw' => ST_DRAW_CARDS,
      'next' => ST_START_OF_TURN,
      'endgame' => ST_PRE_GAME_END,
    ],
  ],

  ST_DISCARD_EXCESS => [
    'name' => 'discardExcess',
    'description' => clienttranslate('${actplayer} must discard ${amount} cards before ending its turn'),
    'descriptionmyturn' => clienttranslate('${you} must discard ${amount} cards before ending your turn'),
    'type' => 'activeplayer',
    'args' => 'argDiscardExcess',
    'possibleactions' => ['cancel', 'discard'],
    'transitions' => [
      'zombiePass' => ST_END_REACT,
      'endTurn' => ST_END_OF_TURN,
      'cancel' => ST_PLAY_CARD,
    ],
  ],

  ST_ELIMINATE => [
    'name' => 'eliminate',
    'description' => '',
    'type' => 'game',
    'action' => 'stEliminate',
    'transitions' => [
      'react' => ST_AWAIT_REACTION,
      'eliminate' => ST_END_REACT,
    ],
  ],

  ST_END_OF_TURN => [
    'name' => 'endOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndOfTurn',
    'transitions' => [
      'next' => ST_NEXT_PLAYER,
    ],
  ],

  ST_NEXT_PLAYER => [
    'name' => 'nextPlayer',
    'description' => '',
    'type' => 'game',
    'action' => 'stNextPlayer',
    'transitions' => [
      'start' => ST_START_OF_TURN,
    ],
    'updateGameProgression' => true,
  ],

  /*
   * Compute winners and losers
   */
  ST_PRE_GAME_END => [
    'name' => 'preGameEnd',
    'description' => '',
    'type' => 'game',
    'action' => 'stPreGameEnd',
    'transitions' => ['' => ST_GAME_END],
  ],

  /*
   * BGA framework final state. Do not modify.
   */
  ST_GAME_END => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd',
  ],
];
