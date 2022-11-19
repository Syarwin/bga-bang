<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * bang implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * bang game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in banghighnoon.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

require_once 'modules/constants.inc.php';

$characters = [
  100 => ['name' => totranslate('Random'), 'tmdisplay' => totranslate('Random')],
  PAUL_REGRET => ['name' => totranslate('Paul Regret'), 'tmdisplay' => totranslate('Paul Regret')],
  SLAB_THE_KILLER => ['name' => totranslate('Slab the Killer'), 'tmdisplay' => totranslate('Slab the Killer')],
  EL_GRINGO => ['name' => totranslate('El Gringo'), 'tmdisplay' => totranslate('El Gringo')],
  ROSE_DOOLAN => ['name' => totranslate('Rose Doolan'), 'tmdisplay' => totranslate('Rose Doolean')],
  KIT_CARLSON => ['name' => totranslate('Kit Carlson'), 'tmdisplay' => totranslate('Kit Carlson')],
  CALAMITY_JANET => ['name' => totranslate('Calamity Janet'), 'tmdisplay' => totranslate('Calamity Janet')],
  PEDRO_RAMIREZ => ['name' => totranslate('Pedro Ramirez'), 'tmdisplay' => totranslate('Pedro Ramirez')],
  SUZY_LAFAYETTE => ['name' => totranslate('Suzy Lafayette'), 'tmdisplay' => totranslate('Suzy Lafayette')],
  BLACK_JACK => ['name' => totranslate('Black Jack'), 'tmdisplay' => totranslate('Black Jack')],
  SID_KETCHUM => ['name' => totranslate('Sid Ketchum'), 'tmdisplay' => totranslate('Sid Ketchum')],
  VULTURE_SAM => ['name' => totranslate('Vulture Sam'), 'tmdisplay' => totranslate('Vulture Sam')],
  BART_CASSIDY => ['name' => totranslate('Bart Cassidy'), 'tmdisplay' => totranslate('Bart Cassidy')],
  JESSE_JONES => ['name' => totranslate('Jesse Jones'), 'tmdisplay' => totranslate('Jesse Jones')],
  JOURDONNAIS => ['name' => totranslate('Jourdonnais'), 'tmdisplay' => totranslate('Jourdonnais')],
  WILLY_THE_KID => ['name' => totranslate('Willy the Kid'), 'tmdisplay' => totranslate('Willy the Kid')],
  LUCKY_DUKE => ['name' => totranslate('Lucky Duke'), 'tmdisplay' => totranslate('Lucky Duke')],
];

$game_options = [
  OPTION_CHOOSE_CHARACTERS => [
    'name' => totranslate('Choose characters'),
    'default' => CHARACTERS_RANDOM,
    'values' => [
      CHARACTERS_RANDOM => [
        'name' => totranslate('Randomly'),
        'description' => totranslate('Character will be chosen randomly for each player'),
      ],
      CHARACTERS_CHOOSE => [
        'name' => totranslate('Manually'),
        'description' => totranslate('Each player chooses one of two random characters on game start'),
      ],
    ],
  ],

  OPTION_EXPANSIONS => [
    'name' => totranslate('Expansions'),
    // TODO: Change to OPTION_BASE_GAME before release
    'default' => OPTION_FISTFUL_OF_CARDS_ONLY,
    'values' => [
      OPTION_BASE_GAME =>
        [ 'name' => totranslate('No'),
          'tmdisplay' => totranslate('No'),
          'description' => totranslate('Base game')],
      OPTION_HIGH_NOON_ONLY =>
        [ 'name' => totranslate('High Noon'),
          'tmdisplay' => totranslate('High Noon expansion'),
          'description' => totranslate('High Noon expansion')],
      OPTION_FISTFUL_OF_CARDS_ONLY =>
        [ 'name' => totranslate('A Fistful Of Cards'),
          'tmdisplay' => totranslate('A Fistful Of Cards expansion'),
          'description' => totranslate('A Fistful Of Cards expansion')],
      OPTION_HIGH_NOON_AND_FOC =>
        [ 'name' => totranslate('High Noon + A Fistful Of Cards'),
          'tmdisplay' => totranslate('High Noon + A Fistful Of Cards'),
          'description' => totranslate('Both High Noon and A Fistful Of Cards expansions. Just 13 random cards will be used')],
    ],
  ],

  OPTION_HIGH_NOON_EXPANSION => [
    'name' => totranslate('High Noon expansion'),
    // TODO: Change to OPTION_HIGH_NOON_NO_GHOST_TOWN before release
    'default' => OPTION_HIGH_NOON_WITH_GHOST_TOWN,
    'values' => [
      OPTION_HIGH_NOON_NO_GHOST_TOWN =>
        [ 'name' => totranslate('Without Ghost Town'),
          'tmdisplay' => totranslate('Without Ghost Town'),
          'description' => totranslate('12 High Noon event cards without a Ghost Town one') ],
      OPTION_HIGH_NOON_WITH_GHOST_TOWN =>
        [ 'name' => totranslate('With Ghost Town'),
          'tmdisplay' => totranslate('With Ghost Town'),
          'description' => totranslate('13 High Noon event cards') ],
    ],
    'displaycondition' => [
        [
          'type' => 'otheroption',
          'id' => OPTION_EXPANSIONS,
          'value' => [OPTION_HIGH_NOON_ONLY]
        ]
      ]
  ],

  OPTION_FISTFUL_OF_CARDS_EXPANSION => [
    'name' => totranslate('A Fistful Of Cards expansion'),
    'default' => OPTION_FISTFUL_OF_CARDS_WITH_DEAD_MAN,
    'values' => [
      OPTION_FISTFUL_OF_CARDS_NO_DEAD_MAN =>
        [ 'name' => totranslate('Without Dead Man'),
          'tmdisplay' => totranslate('Without Dead Man'),
          'description' => totranslate('14 A Fistful Of Cards event cards without a Dead Man one') ],
      OPTION_FISTFUL_OF_CARDS_WITH_DEAD_MAN =>
        [ 'name' => totranslate('With Dead Man'),
          'tmdisplay' => totranslate('With Dead Man'),
          'description' => totranslate('15 A Fistful Of Cards event cards') ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => OPTION_EXPANSIONS,
        'value' => [OPTION_FISTFUL_OF_CARDS_ONLY]
      ]
    ]
  ],

  OPTION_HIGH_NOON_AND_FOC_EXPANSION => [
    'name' => totranslate('High Noon + A Fistful Of Cards expansions'),
    'default' => OPTION_BOTH_EVENTS_WITH_GHOSTS,
    'values' => [
      OPTION_BOTH_EVENTS_NO_GHOSTS =>
        [ 'name' => totranslate('No Dead Man, no Ghost Town'),
          'tmdisplay' => totranslate('No Dead Man, no Ghost Town'),
          'description' => totranslate('13 random event cards, excluding either Dead Man or Ghost Town') ],
      OPTION_BOTH_EVENTS_WITH_GHOSTS =>
        [ 'name' => totranslate('Include Dead Man and Ghost Town'),
          'tmdisplay' => totranslate('Include Dead Man and Ghost Town'),
          'description' => totranslate('13 event cards from both expansions') ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => OPTION_EXPANSIONS,
        'value' => [OPTION_HIGH_NOON_AND_FOC]
      ]
    ]
  ],

  OPTION_CHAR_1 => [
    'name' => totranslate('Character 1'),
    'values' => $characters,
  ],

  OPTION_CHAR_2 => [
    'name' => totranslate('Character 2'),
    'values' => $characters,
  ],

  OPTION_CHAR_3 => [
    'name' => totranslate('Character 3'),
    'values' => $characters,
  ],

  OPTION_CHAR_4 => [
    'name' => totranslate('Character 4'),
    'values' => $characters,
  ],

  OPTION_CHAR_5 => [
    'name' => totranslate('Character 5'),
    'values' => $characters,
  ],

  OPTION_CHAR_6 => [
    'name' => totranslate('Character 6'),
    'values' => $characters,
  ],

  OPTION_CHAR_7 => [
    'name' => totranslate('Character 7'),
    'values' => $characters,
  ],
];

$game_preferences = [
  OPTION_GENERAL_STORE_LAST_CARD => [
    'name' => totranslate('Get General Store cards automatically'),
    'values' => [
      1 => ['name' => totranslate('Get card automatically if possible')],
      0 => ['name' => totranslate('Always choose cards manually')],
    ],
  ],
];
