<?php

require_once 'modules/constants.inc.php';

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
      '' => ST_PRE_CHOOSE_CHARACTER,
    ],
  ],

  ST_PRE_CHOOSE_CHARACTER => [
    'name' => 'preChooseCharacter',
    'type' => 'game',
    'action' => 'stPreChooseCharacter',
    'transitions' => [
      ST_CHOOSE_CHARACTER => ST_CHOOSE_CHARACTER,
      ST_START_OF_TURN => ST_START_OF_TURN,
    ],
  ],

  ST_CHOOSE_CHARACTER => [
    'name' => 'chooseCharacter',
    'description' => clienttranslate('Some players must still choose a character'),
    'descriptionmyturn' => clienttranslate('${you} must choose a character'),
    'type' => 'multipleactiveplayer',
    'args' => 'argChooseCharacter',
    'action' => 'stChooseCharacter',
    'possibleactions' => ['actChooseCharacter'],
    'transitions' => [
      ST_CHARACTER_SETUP => ST_CHARACTER_SETUP,
    ],
  ],

  ST_CHARACTER_SETUP => [
    'name' => 'characterSetup',
    'type' => 'game',
    'action' => 'stCharacterSetup',
    'transitions' => [
      ST_START_OF_TURN => ST_START_OF_TURN,
    ],
  ],

  /*
   * Start of a turn : trigger cards such as Dynamite or Jail before moving on to appropriate state
   */
  ST_START_OF_TURN => [
    'name' => 'startOfTurn',
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
    'type' => 'game',
    'action' => 'stResolveStack',
    'transitions' => [],
  ],

  ST_PRE_PHASE_ONE => [
    'name' => 'prePhaseOne',
    'type' => 'game',
    'action' => 'stPrePhaseOne',
  ],

  ST_PHASE_ONE_SETUP => [
    'name' => 'phaseOneSetup',
    'type' => 'game',
    'action' => 'stPhaseOneSetup',
  ],

  ST_PHASE_ONE_DRAW_CARDS => [
    'name' => 'phaseOneDrawCards',
    'type' => 'game',
    'action' => 'stPhaseOneDrawCards',
  ],

  ST_NEW_EVENT => [
    'name' => 'newEvent',
    'description' => '',
    'type' => 'game',
    'action' => 'stNewEvent',
  ],

  ST_RESOLVE_EVENT_EFFECT => [
    'name' => 'resolveEventEffect',
    'description' => '',
    'type' => 'game',
    'action' => 'stResolveEventEffect',
  ],

  ST_FLIP_CARD => [
    'name' => 'flipCard',
    'type' => 'game',
    'action' => 'stFlipCard',
  ],

  ST_RESOLVE_FLIPPED => [
    'name' => 'resolveFlipped',
    'type' => 'game',
    'action' => 'stResolveFlipped',
  ],

  ST_ACTIVE_DRAW_CARD => [
    'name' => 'drawCard',
    'description' => clienttranslate('${actplayer} must choose where to draw the first card from'),
    'descriptionmyturn' => clienttranslate('${you} must choose where to draw the first card from'),
    'type' => 'activeplayer',
    'args' => 'argDrawCard',
    'possibleactions' => ['actDraw'],
  ],

  ST_PLAY_CARD => [
    'name' => 'playCard',
    'description' => clienttranslate('${actplayer} can play a card'),
    'descriptionmyturn' => clienttranslate('${you} can play a card'),
    'type' => 'activeplayer',
    'args' => 'argPlayCards',
    'action' => 'stPlayCard',
    'possibleactions' => ['actPlayCard', 'actUseAbility', 'actEndTurn'],
  ],

  ST_REACT => [
    'name' => 'react',
    'description' => clienttranslate('${actplayer} must react'),
    'descriptionmyturn' => clienttranslate('${you} must react'),
    'type' => 'activeplayer',
    'args' => 'argReact',
    'action' => 'stReact',
    'possibleactions' => ['actReact', 'actPass', 'actUseAbility'],
  ],

  ST_SELECT_CARD => [
    'name' => 'selectCard',
    'description' => clienttranslate('${actplayer} must select ${amountToPick} cards for the effect of ${src}'),
    'descriptionmyturn' => clienttranslate('${you} must select ${amountToPick} cards for the effect of ${src}'),
    'descriptionsingle' => clienttranslate('${actplayer} must select a card for the effect of ${src}'),
    'descriptionsinglemyturn' => clienttranslate('${you} must select a card for the effect of ${src}'),
    'type' => 'activeplayer',
    'args' => 'argSelect',
    'action' => 'stSelect',
    'possibleactions' => ['actSelect'],
  ],

  ST_REACT_BEER => [
    'name' => 'reactBeer',
    // TODO: Specify Sid Ketchum's ability here and in the case of The Reverend event it should reflect the options correctly
    'description' => clienttranslate('${actplayer} may play ${n} beer to survive'),
    'descriptionmyturn' => clienttranslate('${you} may play ${n} beer to survive'),
    'type' => 'activeplayer',
    'args' => 'argReactBeer',
    'action' => 'stReact',
    'possibleactions' => ['actReact', 'actPass', 'actUseAbility'],
  ],

  ST_DISCARD_EXCESS => [
    'name' => 'discardExcess',
    'description' => clienttranslate('${actplayer} must discard ${amount} cards before ending its turn'),
    'descriptionmyturn' => clienttranslate('${you} must discard ${amount} cards before ending your turn'),
    'type' => 'activeplayer',
    'action' => 'stDiscardExcess',
    'args' => 'argDiscardExcess',
    'possibleactions' => ['actCancelEndTurn', 'actDiscardExcess'],
  ],

  ST_PRE_ELIMINATE_DISCARD => [
    'name' => 'preEliminateDiscard',
    'type' => 'game',
    'action' => 'stPreEliminateDiscard',
  ],

  ST_PRE_ELIMINATE => [
    'name' => 'preEliminate',
    'description' => clienttranslate('${actplayer} must discard all their cards'),
    'descriptionmyturn' => clienttranslate('${you} must select the order in which you want to discard your cards'),
    'type' => 'activeplayer',
    'args' => 'argDiscardEliminate',
    'possibleactions' => ['actDiscardEliminate', 'actDefautDiscardExcess'],
  ],

  ST_ELIMINATE => [
    'name' => 'eliminate',
    'type' => 'game',
    'action' => 'stEliminate',
    'updateGameProgression' => true,
  ],

  ST_VICE_PENALTY => [
    'name' => 'vicePenalty',
    'description' => clienttranslate('${actplayer} must discard all their cards (killing Vice penalty)'),
    'descriptionmyturn' => clienttranslate(
      '${you} must select the order in which you want to discard your cards (killing Vice penalty)'
    ),
    'type' => 'activeplayer',
    'args' => 'argDiscardEliminate',
    'possibleactions' => ['actDiscardVicePenalty', 'actDefautDiscardVicePenalty'],
  ],

  ST_TRIGGER_ABILITY => [
    'name' => 'triggerAbility',
    'type' => 'game',
    'action' => 'stTriggerAbility',
  ],

  ST_CHOOSE_AND_DISCARD_BLUE_CARD => [
    'name' => 'chooseAndDiscardBlueCard',
    'description' => clienttranslate('${actplayer} must discard one blue card in front of them'),
    'descriptionmyturn' => clienttranslate('${you} must discard one blue card in front of you'),
    'type' => 'activeplayer',
    'args' => 'argChooseAndDiscardBlueCard',
    'possibleactions' => ['actDiscardBlue'],
  ],

  ST_DISCARD_BLUE_CARD => [
    'name' => 'discardBlueCard',
    'type' => 'game',
    'action' => 'stDiscardBlueCard',
  ],

  ST_END_OF_TURN => [
    'name' => 'endOfTurn',
    'type' => 'game',
    'action' => 'stEndOfTurn',
    'transitions' => [
      'next' => ST_NEXT_PLAYER,
    ],
  ],

  ST_NEXT_PLAYER => [
    'name' => 'nextPlayer',
    'type' => 'game',
    'action' => 'stNextPlayer',
    'transitions' => [
      'start' => ST_START_OF_TURN,
    ],
    'updateGameProgression' => true,
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
